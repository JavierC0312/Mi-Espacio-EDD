<?php
session_start();
include 'conexion_db.php';

if (!isset($_SESSION["loggedin"]) || !isset($_GET['folio'])) {
    die("Acceso denegado.");
}

$folio = $_GET['folio'];

// 1. OBTENER DATOS GENERALES
$sql = "SELECT 
            d.folio, d.fecha_solicitud, 
            p.nombre, p.ap_paterno, p.ap_materno, p.matricula, p.curp, p.filiacion, p.id_departamento,
            dept.nombre_departamento,
            pt.cuerpo AS plantilla_html, pt.nombre_plantilla, pt.tipo_plantilla
        FROM Documentos d
        JOIN Personal p ON d.matricula_docente_solicitante = p.matricula
        JOIN Departamentos dept ON p.id_departamento = dept.id_departamento
        JOIN PlantillasDocumentos pt ON d.id_plantilla = pt.id_plantilla
        WHERE d.folio = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $folio);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    $matricula_docente = $data['matricula'];
    $id_depto_docente = $data['id_departamento'];
    
    // --- FUNCIONES AUXILIARES ---
    function getAutoridad($conn, $rol, $depto_id = null) {
        $sql = "SELECT CONCAT(nombre, ' ', ap_paterno, ' ', ap_materno) as nombre FROM Personal WHERE tipo_personal = ?";
        if ($depto_id) $sql .= " AND id_departamento = $depto_id";
        $stmt = $conn->prepare($sql . " LIMIT 1");
        $stmt->bind_param("s", $rol);
        $stmt->execute();
        $res = $stmt->get_result();
        return ($res->num_rows > 0) ? $res->fetch_assoc()['nombre'] : "AUTORIDAD NO ASIGNADA";
    }
    function getJefePorDepto($conn, $l) {
        $q="SELECT CONCAT(p.nombre,' ',p.ap_paterno,' ',p.ap_materno) as n FROM Personal p JOIN Departamentos d ON p.id_departamento=d.id_departamento WHERE p.tipo_personal='JEFE_AREA' AND d.nombre_departamento LIKE ? LIMIT 1";
        $p="%$l%"; $s=$conn->prepare($q); $s->bind_param("s",$p); $s->execute(); return $s->get_result()->fetch_assoc()['n']??"AUTORIDAD NO ASIGNADA";
    }

    // --- AUTORIDADES ---
    $director = getAutoridad($conn, 'DIRECTOR');
    $subdirector = getAutoridad($conn, 'SUBDIRECTOR');
    $jefe_propio = getAutoridad($conn, 'JEFE_AREA', $id_depto_docente);
    $jefe_rh = getJefePorDepto($conn, 'Recursos');
    $jefe_escolares = getJefePorDepto($conn, 'Escolares');
    $jefe_desarrollo = getJefePorDepto($conn, 'Desarrollo');
    
    $presidente_academia = getAutoridad($conn, 'DOCENTE', $id_depto_docente); 

    // --- VARIABLES BASE ---
    $nombre_completo = $data['nombre'] . ' ' . $data['ap_paterno'] . ' ' . $data['ap_materno'];
    $anio_actual = date('Y');
    
    $vars = [
        '{{nombre_completo}}' => $nombre_completo,
        '{{matricula}}' => $data['matricula'],
        '{{curp}}' => $data['curp'],
        '{{rfc}}' => substr($data['curp'], 0, 10),
        '{{filiacion}}' => $data['filiacion'] ?? 'N/A',
        '{{nombre_departamento}}' => $data['nombre_departamento'],
        '{{folio}}' => $folio,
        
        // FECHAS
        '{{dia_actual}}' => date('d'), '{{dia_letra}}' => date('d'), '{{dia_actual_letra}}' => date('d'),
        '{{mes_actual}}' => obtenerMesEspanol(date('m')), '{{mes_letra}}' => obtenerMesEspanol(date('m')),
        '{{anio_actual}}' => $anio_actual, '{{anio_letra}}' => $anio_actual, '{{anio_actual_letra}}' => $anio_actual,
        '{{anio}}' => $anio_actual, '{{anio_anterior}}' => ($anio_actual - 1),
        '{{fecha_actual}}' => date('d/m/Y'),
        '{{fecha_actual_texto}}' => date('d') . ' de ' . obtenerMesEspanol(date('m')) . ' de ' . $anio_actual,
        '{{fecha_actual_completa}}' => date('d') . ' de ' . obtenerMesEspanol(date('m')) . ' de ' . $anio_actual,
        '{{fecha_texto_completa}}' => date('d') . ' de ' . obtenerMesEspanol(date('m')) . ' de ' . $anio_actual,

        // NOMBRES AUTORIDADES
        '{{nombre_director}}' => $director,
        '{{nombre_subdirector}}' => $subdirector,
        '{{nombre_jefe_depto}}' => $jefe_propio,
        '{{nombre_jefe_rh}}' => $jefe_rh, '{{nombre_jefa_rh}}' => $jefe_rh,
        '{{nombre_jefe_escolares}}' => $jefe_escolares, '{{nombre_jefa_escolares}}' => $jefe_escolares,
        '{{nombre_jefe_desarrollo}}' => $jefe_desarrollo, '{{nombre_jefa_desarrollo}}' => $jefe_desarrollo,
        '{{nombre_presidente_academia}}' => $presidente_academia, '{{nombre_academia}}' => $data['nombre_departamento'],
        
        // DEFAULTS
        '{{periodo}}' => 'AGO-DIC 2024',
        '{{clave_presupuestal}}' => 'N/A', '{{categoria}}' => 'N/A', '{{horas}}' => '0', 
        '{{fecha_efectos}}' => 'N/A', '{{estatus_plaza}}' => 'N/A', '{{fecha_ingreso}}' => 'N/A',
        '{{registro_cvu}}' => 'N/A',
        '{{filas_horario_clases}}' => '<tr><td colspan="11">Sin carga</td></tr>',
        '{{filas_actividades_apoyo}}' => '<tr><td colspan="8">Sin actividades</td></tr>',
        '{{filas_materias_impartidas}}' => '<tr><td colspan="5">Sin materias</td></tr>',
        '{{total_alumnos}}' => '0',
        '{{descripcion_actividad}}' => 'realizó actividades académicas',
        '{{tabla_detalles_actividad}}' => '',
        '{{periodo_1}}' => '', '{{cantidad_1}}' => '', '{{carrera_1}}' => '',
        '{{periodo_2}}' => '', '{{cantidad_2}}' => '', '{{carrera_2}}' => '', '{{total_tutorados}}' => '0',
        '{{nombre_alumno}}' => '', '{{numero_control}}' => '', '{{carrera_alumno}}' => '', '{{opcion_titulacion}}' => '',
        '{{nombre_proyecto}}' => '', '{{folio_acta}}' => '', '{{nombre_presidente_jurado}}' => '',
        '{{cedula_presidente}}' => '', '{{nombre_secretario_jurado}}' => '', '{{cedula_secretario}}' => '',
        '{{nombre_vocal_jurado}}' => '', '{{cedula_vocal}}' => ''
    ];

    // --- LÓGICA ESPECÍFICA (PLAZA, HORARIO, ETC.) ---
    $sql_p = "SELECT categoria, horas, clave_presupuestal, fecha_efectos, estatus FROM Plazas WHERE matricula_personal = ? AND es_actual = 1 LIMIT 1";
    $stmt_p = $conn->prepare($sql_p); $stmt_p->bind_param("s", $matricula_docente); $stmt_p->execute();
    if ($rp = $stmt_p->get_result()->fetch_assoc()) { $vars['{{categoria}}'] = $rp['categoria']; $vars['{{horas}}'] = $rp['horas']; $vars['{{clave_presupuestal}}'] = $rp['clave_presupuestal']; $vars['{{fecha_efectos}}'] = $rp['fecha_efectos']; $vars['{{estatus_plaza}}'] = $rp['estatus']; } $stmt_p->close();
    
    $sql_i = "SELECT fecha_ingreso, registro_cvu FROM Docentes d JOIN Personal p ON d.matricula_personal=p.matricula WHERE matricula = ?";
    $stmt_i = $conn->prepare($sql_i); $stmt_i->bind_param("s", $matricula_docente); $stmt_i->execute();
    if ($ri = $stmt_i->get_result()->fetch_assoc()) { $vars['{{fecha_ingreso}}'] = $ri['fecha_ingreso']; $vars['{{registro_cvu}}'] = $ri['registro_cvu']; }

    if (stripos($data['nombre_plantilla'], 'Tutoría') !== false) {
        $sql_t = "SELECT p.nombre_periodo, gt.alumnos_atendidos, gt.carrera FROM GruposTutoria gt JOIN PeriodosEscolares p ON gt.id_periodo = p.id_periodo WHERE gt.matricula_docente = ? ORDER BY p.fecha_inicio DESC";
        $stmt_t = $conn->prepare($sql_t); $stmt_t->bind_param("s", $matricula_docente); $stmt_t->execute(); $res_t = $stmt_t->get_result(); $c = 1; $tot = 0;
        while($r = $res_t->fetch_assoc()) { if($c <= 2) { $vars["{{periodo_$c}}"] = $r['nombre_periodo']; $vars["{{cantidad_$c}}"] = $r['alumnos_atendidos']; $vars["{{carrera_$c}}"] = $r['carrera']; } $tot += $r['alumnos_atendidos']; $c++; } $vars['{{total_tutorados}}'] = $tot;
    }

    if (stripos($data['nombre_plantilla'], 'Horario') !== false) {
        $sql_h = "SELECT m.nombre_materia, g.id_grupo, m.clave_materia, m.nivel, h.dia_semana, h.hora_inicio, h.hora_fin FROM Grupos g JOIN Materias m ON g.clave_materia=m.clave_materia LEFT JOIN Horarios h ON g.id_grupo=h.id_grupo WHERE g.matricula_docente = ?";
        $stmt_h = $conn->prepare($sql_h); $stmt_h->bind_param("s", $matricula_docente); $stmt_h->execute(); $res_h = $stmt_h->get_result(); $map = []; $tot_h = 0;
        while($row = $res_h->fetch_assoc()) { $id = $row['id_grupo']; if(!isset($map[$id])) $map[$id] = ['nom'=>$row['nombre_materia'], 'cve'=>$row['clave_materia'], 'niv'=>($row['nivel']=='LICENCIATURA'?'LI':'PO'), 'd'=>array_fill(1,6,''), 'h'=>0]; if($row['dia_semana']) { $ini = substr($row['hora_inicio'],0,5); $fin = substr($row['hora_fin'],0,5); $map[$id]['d'][$row['dia_semana']] = "$ini-$fin"; $diff = ((int)substr($fin,0,2) - (int)substr($ini,0,2)); $map[$id]['h'] += $diff; $tot_h += $diff; } }
        $html_h = ""; foreach($map as $m) { $html_h .= "<tr><td style='text-align:left;font-size:6pt'>{$m['nom']}</td><td>{$m['cve']}</td><td>A</td><td>{$m['niv']}</td><td>PR</td><td>ISC</td>"; for($i=1;$i<=6;$i++) $html_h .= "<td>{$m['d'][$i]}</td>"; $html_h .= "<td>{$m['h']}</td></tr>"; } $vars['{{filas_horario_clases}}'] = $html_h ?: "<tr><td colspan='11'>Sin carga</td></tr>"; $vars['{{horas_preparacion}}'] = 40 - $tot_h;
        $sql_a = "SELECT nombre_actividad FROM ActividadesDocente WHERE matricula_docente = ?"; $stmt_a = $conn->prepare($sql_a); $stmt_a->bind_param("s", $matricula_docente); $stmt_a->execute(); $res_a = $stmt_a->get_result(); $html_a = ""; while($row = $res_a->fetch_assoc()) $html_a .= "<tr><td style='text-align:left'>{$row['nombre_actividad']}</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>"; $vars['{{filas_actividades_apoyo}}'] = $html_a ?: "<tr><td colspan='8'>Sin actividades</td></tr>";
    }

    if (stripos($data['nombre_plantilla'], 'Académica') !== false || stripos($data['nombre_plantilla'], 'Comisión') !== false) {
        $sql_act = "SELECT tipo_actividad, nombre_actividad, descripcion FROM ActividadesDocente WHERE matricula_docente = ? ORDER BY id_actividad DESC LIMIT 1";
        $stmt_act = $conn->prepare($sql_act); $stmt_act->bind_param("s", $matricula_docente); $stmt_act->execute();
        if ($row_act = $stmt_act->get_result()->fetch_assoc()) {
            $vars['{{descripcion_actividad}}'] = "implementó la estrategia didáctica: <strong>" . $row_act['nombre_actividad'] . "</strong>";
            $vars['{{tabla_detalles_actividad}}'] = '<table style="width:100%;border-collapse:collapse;border:1px solid black"><tr style="background-color:#f2f2f2"><th style="border:1px solid black;padding:5px">Actividad</th><th style="border:1px solid black;padding:5px">Detalles</th></tr><tr><td style="border:1px solid black;padding:5px">'.$row_act['tipo_actividad'].'</td><td style="border:1px solid black;padding:5px">'.($row_act['descripcion']??'').'</td></tr></table>';
        }
    }

    if (stripos($data['nombre_plantilla'], 'Escolares') !== false || stripos($data['nombre_plantilla'], 'Servicios') !== false) {
        
        // Grado Académico
        $sql_g = "SELECT grado_estudios FROM Docentes WHERE matricula_personal = ?";
        $stmt_g = $conn->prepare($sql_g); 
        $stmt_g->bind_param("s", $matricula_docente); 
        $stmt_g->execute();
        $res_g = $stmt_g->get_result();
        
        // CAMBIO 2: Usamos un operador ternario (?) para poner un default si no hay grado
        $vars['{{grado_academico}}'] = ($row = $res_g->fetch_assoc()) ? $row['grado_estudios'] : 'Docente';
        $stmt_g->close();

        // Materias (Tabla)
        $sql_m = "SELECT m.nombre_materia, m.clave_materia, m.nivel, g.alumnos_atendidos, p.nombre_periodo 
                  FROM Grupos g 
                  JOIN Materias m ON g.clave_materia = m.clave_materia 
                  JOIN PeriodosEscolares p ON g.id_periodo = p.id_periodo 
                  WHERE g.matricula_docente = ?";
        $stmt_m = $conn->prepare($sql_m);
        $stmt_m->bind_param("s", $matricula_docente);
        $stmt_m->execute();
        $res_m = $stmt_m->get_result();
        
        $html_m = ""; 
        $tot_a = 0; 
        $per = "Periodo Actual"; // CAMBIO 3: Valor por defecto para que no salga vacío

        if ($res_m->num_rows > 0) {
            while ($r = $res_m->fetch_assoc()) {
                $html_m .= "<tr>
                    <td>{$r['nombre_periodo']}</td>
                    <td>{$r['nivel']}</td>
                    <td>{$r['clave_materia']}</td>
                    <td style='text-align:left'>{$r['nombre_materia']}</td>
                    <td>{$r['alumnos_atendidos']}</td>
                </tr>";
                $tot_a += $r['alumnos_atendidos'];
                $per = $r['nombre_periodo'];
            }
        } else {
            $html_m = "<tr><td colspan='5'>No se encontraron materias registradas.</td></tr>";
        }

        $vars['{{filas_materias_impartidas}}'] = $html_m;
        $vars['{{total_alumnos}}'] = $tot_a;
        $vars['{{periodos_evaluados}}'] = $per;
    }

    if (stripos($data['nombre_plantilla'], 'Examen') !== false) {
        $sql_acta = "SELECT a.*, al.nombre as an, al.ap_paterno as ap, al.ap_materno as am, al.numero_control, c.nombre_carrera, CONCAT(p1.nombre,' ',p1.ap_paterno,' ',p1.ap_materno) as np, p1.matricula as cp, CONCAT(p2.nombre,' ',p2.ap_paterno,' ',p2.ap_materno) as ns, p2.matricula as cs, CONCAT(p3.nombre,' ',p3.ap_paterno,' ',p3.ap_materno) as nv, p3.matricula as cv FROM ActasTitulacion a JOIN Alumnos al ON a.numero_control_alumno = al.numero_control JOIN Carreras c ON al.id_carrera = c.id_carrera JOIN Personal p1 ON a.matricula_presidente = p1.matricula JOIN Personal p2 ON a.matricula_secretario = p2.matricula JOIN Personal p3 ON a.matricula_vocal = p3.matricula LIMIT 1";
        $res_ex = $conn->query($sql_acta);
        if ($rex = $res_ex->fetch_assoc()) {
            $vars['{{nombre_alumno}}'] = $rex['an'].' '.$rex['ap'].' '.$rex['am']; $vars['{{numero_control}}'] = $rex['numero_control']; $vars['{{carrera_alumno}}'] = $rex['nombre_carrera']; $vars['{{clave_escuela}}'] = '25DIT0002I'; $vars['{{opcion_titulacion}}'] = $rex['opcion_titulacion']; $vars['{{nombre_proyecto}}'] = '"'.$rex['nombre_proyecto'].'"'; $vars['{{folio_acta}}'] = $rex['folio_acta']; $vars['{{nombre_presidente_jurado}}'] = $rex['np']; $vars['{{cedula_presidente}}'] = $rex['cp']; $vars['{{nombre_secretario_jurado}}'] = $rex['ns']; $vars['{{cedula_secretario}}'] = $rex['cs']; $vars['{{nombre_vocal_jurado}}'] = $rex['nv']; $vars['{{cedula_vocal}}'] = $rex['cv'];
            $f = strtotime($rex['fecha_acto']); $vars['{{dia}}'] = date('d',$f); $vars['{{mes}}'] = obtenerMesEspanol(date('m',$f)); $vars['{{anio}}'] = date('Y',$f);
        }
    }


    // --- INYECTAR FIRMAS (SI YA SE FIRMARON) ---
    $sql_f = "SELECT rol_firmante_en_acto, ruta_qr_snapshot FROM FirmasDocumento WHERE folio_documento = '$folio'";
    $res_f = $conn->query($sql_f);
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $ruta_web_base = "$protocol://" . $_SERVER['HTTP_HOST'] . "/Mi_Espacio_EDD/";

    while($rf = $res_f->fetch_assoc()) {
        $hueco = $rf['rol_firmante_en_acto']; 
        $img = $ruta_web_base . $rf['ruta_qr_snapshot'];
        $vars[$hueco] = "<img src='$img' style='width:90px; display:block; margin:0 auto;'>";
    }


    // --- RENDER FINAL ---
    $html = $data['plantilla_html'];
    
    // 1. Reemplazar variables y firmas existentes
    foreach ($vars as $key => $val) {
        $html = str_replace($key, (string)$val, $html);
    }

    // 2. MAGIC FIX: Reemplazar cualquier {{firma_...}} que SOBRÓ por el recuadro gris
    // Esto es lo que faltaba: una expresión regular que busca CUALQUIER cosa como {{firma_loquesea}}
    // y lo convierte en el cuadro de "Espacio para Firma"
    $recuadro_gris = '<div style="border:1px dashed #ccc; color:#ccc; font-size:9px; padding:15px; display:inline-block; width:130px; text-align:center; background-color:#f9f9f9; margin-bottom:5px;">(Espacio para Firma)</div>';
    $html = preg_replace('/{{firma_[a-z_]+}}/', $recuadro_gris, $html);

    // 3. Rutas absolutas de imágenes
    $ruta_base = "$protocol://" . $_SERVER['HTTP_HOST'] . "/Mi_Espacio_EDD/archivos/recursos_graficos/";
    $html = str_replace(['../../archivos/recursos_graficos/', '../archivos/recursos_graficos/', 'archivos/recursos_graficos/'], $ruta_base, $html);
    $html = str_replace('logo_tecnm_vertical.png', 'logo_tecnm.png', $html);

    $css_hoja = (stripos($data['nombre_plantilla'], 'Horario') !== false) ? 'width: 28cm; height: 21.5cm;' : 'width: 21.5cm; min-height: 27.9cm;';

    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><style>
            body { font-family: Arial, sans-serif; background-color: #525659; margin: 0; padding: 20px; display: flex; justify-content: center; }
            .hoja { background: white; ' . $css_hoja . ' padding: 1.5cm; box-shadow: 0 0 10px rgba(0,0,0,0.5); box-sizing: border-box; position: relative; } 
            table { width: 100%; border-collapse: collapse; } img { max-width: 100%; }
          </style></head><body><div class="hoja">' . $html . '</div></body></html>';

} else {
    echo "Documento no encontrado.";
}

function obtenerMesEspanol($numMes) {
    $meses = ["01"=>"Enero","02"=>"Febrero","03"=>"Marzo","04"=>"Abril","05"=>"Mayo","06"=>"Junio","07"=>"Julio","08"=>"Agosto","09"=>"Septiembre","10"=>"Octubre","11"=>"Noviembre","12"=>"Diciembre"];
    return $meses[$numMes] ?? $numMes;
}
?>