<?php
session_start();
include 'conexion_db.php';

if (!file_exists('libs/tcpdf/tcpdf.php')) { die(json_encode(['success'=>false, 'message'=>'Falta TCPDF'])); }
require_once('libs/tcpdf/tcpdf.php');
function obtenerMesEspanol($n) { $m=["01"=>"Enero","02"=>"Febrero","03"=>"Marzo","04"=>"Abril","05"=>"Mayo","06"=>"Junio","07"=>"Julio","08"=>"Agosto","09"=>"Septiembre","10"=>"Octubre","11"=>"Noviembre","12"=>"Diciembre"]; return $m[$n]??$n; }

$response = ['success' => false, 'message' => 'Error.'];

if (isset($_POST['folio'])) {
    $folio = $_POST['folio'];
    $matricula_usuario = $_SESSION['usuario_id']; 

    // 1. DATOS USUARIO
    $sql_u = "SELECT ruta_firma_qr, tipo_personal, id_departamento FROM Personal WHERE matricula = ?";
    $stmt_u = $conn->prepare($sql_u); $stmt_u->bind_param("s", $matricula_usuario); $stmt_u->execute();
    $data_user = $stmt_u->get_result()->fetch_assoc();
    
    if (!$data_user['ruta_firma_qr']) die(json_encode(['success'=>false, 'message'=>'No tienes firma digital.']));
    $ruta_qr_user = $data_user['ruta_firma_qr'];
    $rol_user = $data_user['tipo_personal'];
    $depto_user = $data_user['id_departamento'];

    // 2. DATOS DOCUMENTO
    $sql_doc = "SELECT d.matricula_docente_solicitante, d.id_plantilla, pt.cuerpo, d.ruta_pdf, pt.nombre_plantilla FROM Documentos d JOIN PlantillasDocumentos pt ON d.id_plantilla = pt.id_plantilla WHERE d.folio = ?";
    $stmt_d = $conn->prepare($sql_doc); $stmt_d->bind_param("s", $folio); $stmt_d->execute();
    $data_doc = $stmt_d->get_result()->fetch_assoc();
    
    $html_plantilla = $data_doc['cuerpo'];
    $docente_solicitante = $data_doc['matricula_docente_solicitante'];
    $id_plantilla = $data_doc['id_plantilla'];
    $es_correccion_manual = ($data_doc['ruta_pdf'] && strpos($data_doc['ruta_pdf'], 'CORREGIDO_') !== false);

    // 3. DETERMINAR HUECO DE FIRMA
    $mi_hueco = null;

    // --- LÓGICA ESPECIAL: JURADO DE EXAMEN (ID 9) ---
    if ($id_plantilla == 9) {
        // Consultamos el acta para ver qué rol juega ESTE usuario (sea quien sea)
        // Nota: Asumimos que hay un acta vinculada a este proceso (en producción vincularíamos por folio/id)
        // Aquí buscamos un acta donde este usuario sea parte del jurado.
        $sql_jurado = "SELECT matricula_presidente, matricula_secretario, matricula_vocal FROM ActasTitulacion 
                       WHERE matricula_presidente = ? OR matricula_secretario = ? OR matricula_vocal = ? LIMIT 1";
        $stmt_j = $conn->prepare($sql_jurado);
        $stmt_j->bind_param("sss", $matricula_usuario, $matricula_usuario, $matricula_usuario);
        $stmt_j->execute();
        $res_jurado = $stmt_j->get_result();

        if ($row_j = $res_jurado->fetch_assoc()) {
            if ($row_j['matricula_presidente'] == $matricula_usuario) $mi_hueco = '{{firma_presidente}}';
            elseif ($row_j['matricula_secretario'] == $matricula_usuario) $mi_hueco = '{{firma_secretario}}';
            elseif ($row_j['matricula_vocal'] == $matricula_usuario) $mi_hueco = '{{firma_vocal}}';
        }
    }
    
    // --- LÓGICA ESTÁNDAR (Si no cayó en caso especial) ---
    if (!$mi_hueco) {
        if ($rol_user == 'DOCENTE' && $matricula_usuario == $docente_solicitante) {
            $mi_hueco = '{{firma_docente}}';
        } 
        elseif ($rol_user == 'DIRECTOR') $mi_hueco = '{{firma_director}}';
        elseif ($rol_user == 'SUBDIRECTOR') $mi_hueco = '{{firma_subdirector}}';
        elseif ($rol_user == 'JEFE_AREA') {
            if (strpos($html_plantilla, '{{firma_jefe_rh}}') !== false && $depto_user == 2) $mi_hueco = '{{firma_jefe_rh}}';
            else if (strpos($html_plantilla, '{{firma_jefe_escolares}}') !== false && $depto_user == 5) $mi_hueco = '{{firma_jefe_escolares}}';
            else if (strpos($html_plantilla, '{{firma_jefe_desarrollo}}') !== false && $depto_user == 6) $mi_hueco = '{{firma_jefe_desarrollo}}';
            else $mi_hueco = '{{firma_jefe}}';
        }
    }

    // Validación Final
    if ((!$mi_hueco || strpos($html_plantilla, $mi_hueco) === false) && !$es_correccion_manual) {
        die(json_encode(['success'=>false, 'message'=>'No tienes un rol asignado para firmar este documento.']));
    }

    // 4. REGISTRAR FIRMA
    $check = $conn->query("SELECT id_firma FROM FirmasDocumento WHERE folio_documento='$folio' AND matricula_firmante='$matricula_usuario'");
    if ($check->num_rows == 0 && $mi_hueco) {
        $token = md5($folio . $matricula_usuario . time());
        // AQUÍ ESTABA EL ERROR DE SUPERPOSICIÓN: Guardábamos mal el rol.
        // Ahora guardamos $mi_hueco (ej. {{firma_secretario}}) explícitamente
        $stmt_ins = $conn->prepare("INSERT INTO FirmasDocumento (folio_documento, matricula_firmante, rol_firmante_en_acto, token_firma, ruta_qr_snapshot) VALUES (?, ?, ?, ?, ?)");
        $stmt_ins->bind_param("sssss", $folio, $matricula_usuario, $mi_hueco, $token, $ruta_qr_user);
        $stmt_ins->execute();
    }

    // 5. VERIFICAR SI FALTAN FIRMAS
    $res_firmas = $conn->query("SELECT rol_firmante_en_acto FROM FirmasDocumento WHERE folio_documento = '$folio'");
    $firmas_hechas = [];
    while($r = $res_firmas->fetch_assoc()) $firmas_hechas[] = $r['rol_firmante_en_acto'];

    preg_match_all('/{{firma_[a-z_]+}}/', $html_plantilla, $matches);
    $firmas_requeridas = array_unique($matches[0]);
    
    $faltan = false;
    foreach ($firmas_requeridas as $req) {
        if (!in_array($req, $firmas_hechas)) { $faltan = true; break; }
    }

    if ($es_correccion_manual) {
        $conn->query("UPDATE Documentos SET estado='Completado', fecha_completado=NOW() WHERE folio='$folio'");
        echo json_encode(['success'=>true, 'message'=>'Completado.']); exit;
    }

    if ($faltan) {
        $conn->query("UPDATE Documentos SET estado='En Proceso' WHERE folio='$folio'");
        echo json_encode(['success'=>true, 'message'=>'Firma registrada. Faltan otras firmas.']); exit;
    }

    // =======================================================================
    // 6. GENERAR PDF FINAL
    // =======================================================================
    if (ob_get_length()) ob_clean();
    $final_file_name = "FINAL_" . $folio . ".pdf"; 
    $final_file_path = "archivos/docs_generados/" . $final_file_name;

    // Recopilar datos (Versión compacta de api_ver_documento)
    $sql_full = "SELECT d.folio, p.nombre, p.ap_paterno, p.ap_materno, p.matricula, p.curp, p.filiacion, p.id_departamento, dept.nombre_departamento, pt.cuerpo, pt.nombre_plantilla FROM Documentos d JOIN Personal p ON d.matricula_docente_solicitante = p.matricula JOIN Departamentos dept ON p.id_departamento = dept.id_departamento JOIN PlantillasDocumentos pt ON d.id_plantilla = pt.id_plantilla WHERE d.folio = '$folio'";
    $data_full = $conn->query($sql_full)->fetch_assoc();
    
    // Preparar variables básicas (Igual que antes)
    $matricula_docente = $data_full['matricula'];
    $nombre_docente = $data_full['nombre'].' '.$data_full['ap_paterno'].' '.$data_full['ap_materno'];
    
    // --- VARIABLES ---
    $vars = [
        '{{nombre_completo}}' => $nombre_docente, // CORREGIDO: Usamos $nombre_docente
        '{{matricula}}' => $data_full['matricula'], // CORREGIDO: Usamos $data_full
        '{{curp}}' => $data_full['curp'],
        '{{rfc}}' => substr($data_full['curp'], 0, 10),
        '{{filiacion}}' => $data_full['filiacion'] ?? 'N/A',
        '{{nombre_departamento}}' => $data_full['nombre_departamento'],
        '{{folio}}' => $folio,
        
        // Fechas
        '{{dia_actual}}' => date('d'), '{{dia_letra}}' => date('d'), '{{dia_actual_letra}}' => date('d'),
        '{{mes_actual}}' => obtenerMesEspanol(date('m')), '{{mes_letra}}' => obtenerMesEspanol(date('m')),
        '{{anio_actual}}' => $anio_actual, '{{anio_letra}}' => $anio_actual, '{{anio_actual_letra}}' => $anio_actual,
        '{{anio}}' => $anio_actual, '{{anio_anterior}}' => $anio_actual - 1,
        '{{fecha_actual}}' => date('d/m/Y'),
        '{{fecha_actual_texto}}' => date('d').' de '.obtenerMesEspanol(date('m')).' de '.$anio_actual,
        '{{fecha_actual_completa}}' => date('d').' de '.obtenerMesEspanol(date('m')).' de '.$anio_actual,
        '{{fecha_texto_completa}}' => date('d').' de '.obtenerMesEspanol(date('m')).' de '.$anio_actual,

        // Autoridades
        '{{nombre_director}}' => $director, '{{nombre_subdirector}}' => $subdirector,
        '{{nombre_jefe_depto}}' => $jefe_propio,
        '{{nombre_jefe_rh}}' => $jefe_rh, '{{nombre_jefa_rh}}' => $jefe_rh,
        '{{nombre_jefe_escolares}}' => $jefe_esc, '{{nombre_jefa_escolares}}' => $jefe_esc,
        '{{nombre_jefe_desarrollo}}' => $jefe_des, '{{nombre_jefa_desarrollo}}' => $jefe_des,
        '{{nombre_presidente_academia}}' => $nombre_docente,

        // Defaults
        '{{periodo}}' => 'AGO-DIC 2024', '{{filas_horario_clases}}' => '', '{{horas_preparacion}}' => '0',
        '{{filas_actividades_apoyo}}' => '', '{{filas_materias_impartidas}}' => '', '{{total_alumnos}}' => '0',
        '{{grado_academico}}' => 'C.', '{{clave_presupuestal}}' => '', '{{categoria}}' => '', '{{horas}}' => '',
        '{{fecha_efectos}}' => '', '{{estatus_plaza}}' => '', '{{fecha_ingreso}}' => '', '{{registro_cvu}}' => '',
        '{{descripcion_actividad}}' => '', '{{tabla_detalles_actividad}}' => '',
        '{{total_tutorados}}' => '0', '{{periodo_1}}' => '', '{{cantidad_1}}' => '', '{{carrera_1}}' => '',
        '{{periodo_2}}' => '', '{{cantidad_2}}' => '', '{{carrera_2}}' => '',
        '{{nombre_alumno}}' => '', '{{numero_control}}' => '', '{{carrera_alumno}}' => '', '{{opcion_titulacion}}' => '',
        '{{nombre_proyecto}}' => '', '{{folio_acta}}' => '', '{{nombre_presidente_jurado}}' => '',
        '{{cedula_presidente}}' => '', '{{nombre_secretario_jurado}}' => '', '{{cedula_secretario}}' => '',
        '{{nombre_vocal_jurado}}' => '', '{{cedula_vocal}}' => '', '{{dia}}' => '', '{{mes}}' => '', '{{anio}}' => ''
    ];
        
    $vars = [ '{{folio}}'=>$folio, '{{nombre_completo}}'=>$nombre_docente ]; // Inicializar mínimo
    
    // Lógica EXAMEN PROFESIONAL (Requerida para llenar los nombres del jurado)
    if ($id_plantilla == 9) {
        $sql_acta = "SELECT a.*, al.nombre as an, al.ap_paterno as ap, al.ap_materno as am, al.numero_control, c.nombre_carrera, CONCAT(p1.nombre,' ',p1.ap_paterno,' ',p1.ap_materno) as np, p1.matricula as cp, CONCAT(p2.nombre,' ',p2.ap_paterno,' ',p2.ap_materno) as ns, p2.matricula as cs, CONCAT(p3.nombre,' ',p3.ap_paterno,' ',p3.ap_materno) as nv, p3.matricula as cv FROM ActasTitulacion a JOIN Alumnos al ON a.numero_control_alumno = al.numero_control JOIN Carreras c ON al.id_carrera = c.id_carrera JOIN Personal p1 ON a.matricula_presidente = p1.matricula JOIN Personal p2 ON a.matricula_secretario = p2.matricula JOIN Personal p3 ON a.matricula_vocal = p3.matricula LIMIT 1";
        $rex = $conn->query($sql_acta)->fetch_assoc();
        if($rex) {
             $vars['{{nombre_alumno}}'] = $rex['an'].' '.$rex['ap'].' '.$rex['am'];
             $vars['{{numero_control}}'] = $rex['numero_control'];
             $vars['{{carrera_alumno}}'] = $rex['nombre_carrera'];
             $vars['{{clave_escuela}}'] = '25DIT0002I';
             $vars['{{opcion_titulacion}}'] = $rex['opcion_titulacion'];
             $vars['{{nombre_proyecto}}'] = '"'.$rex['nombre_proyecto'].'"';
             $vars['{{folio_acta}}'] = $rex['folio_acta'];
             $vars['{{nombre_presidente_jurado}}'] = $rex['np']; $vars['{{cedula_presidente}}'] = $rex['cp'];
             $vars['{{nombre_secretario_jurado}}'] = $rex['ns']; $vars['{{cedula_secretario}}'] = $rex['cs'];
             $vars['{{nombre_vocal_jurado}}'] = $rex['nv']; $vars['{{cedula_vocal}}'] = $rex['cv'];
             $f=strtotime($rex['fecha_acto']); $vars['{{dia}}']=date('d',$f); $vars['{{mes}}']=obtenerMesEspanol(date('m',$f)); $vars['{{anio}}']=date('Y',$f);
        }
    }

    // Fusionar firmas
    $res_qrs = $conn->query("SELECT rol_firmante_en_acto, ruta_qr_snapshot FROM FirmasDocumento WHERE folio_documento = '$folio'");
    while($r = $res_qrs->fetch_assoc()) {
        $path = __DIR__ . '/' . $r['ruta_qr_snapshot'];
        if (file_exists($path)) $vars[$r['rol_firmante_en_acto']] = '<img src="'.$path.'" width="90" />';
    }

    // Render
    $html = $data_full['cuerpo'];
    foreach ($vars as $key => $val) $html = str_replace($key, (string)$val, $html);
    $base_path = __DIR__ . '/archivos/recursos_graficos/';
    $html = str_replace(['../../archivos/recursos_graficos/', 'archivos/recursos_graficos/'], $base_path, $html);

    // PDF
    $pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('Mi Espacio EDD');
    $pdf->setPrintHeader(false); $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 15, 15); $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->AddPage();
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output(__DIR__ . '/' . $final_file_path, 'F');

    $conn->query("UPDATE Documentos SET estado='Completado', fecha_completado=NOW(), ruta_pdf='$final_file_path' WHERE folio='$folio'");
    echo json_encode(['success'=>true, 'message'=>'Documento completado.']);
}
?>