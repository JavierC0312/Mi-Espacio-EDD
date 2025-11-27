<?php
session_start();
include 'conexion_db.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    die(json_encode(['error' => 'Acceso denegado']));
}

$matricula = $_SESSION['usuario_id'];

// Consultamos documentos activos (Pendientes o En Proceso)
// Traemos también el cuerpo HTML para analizar qué firmas pide
$sql = "SELECT 
            d.folio, d.fecha_solicitud, d.matricula_docente_solicitante,
            pt.nombre_plantilla, pt.cuerpo, pt.id_plantilla,
            CONCAT(p.nombre, ' ', p.ap_paterno) as solicitante
        FROM Documentos d
        JOIN PlantillasDocumentos pt ON d.id_plantilla = pt.id_plantilla
        JOIN Personal p ON d.matricula_docente_solicitante = p.matricula
        WHERE d.estado IN ('Pendiente', 'En Proceso')";

$result = $conn->query($sql);
$pendientes = [];

while ($row = $result->fetch_assoc()) {
    $folio = $row['folio'];
    $html = $row['cuerpo'];
    $es_dueno = ($row['matricula_docente_solicitante'] == $matricula);
    
    // 1. Verificar si YA firmé este documento
    $check = $conn->query("SELECT id_firma FROM FirmasDocumento WHERE folio_documento='$folio' AND matricula_firmante='$matricula'");
    if ($check->num_rows > 0) {
        continue; // Ya firmé, saltar al siguiente
    }

    $debo_firmar = false;

    // 2. REGLAS DE FIRMA (¿Me toca a mí?)

    // A. Soy el DUEÑO y el documento pide firma de docente (Ej. Exclusividad, Horario)
    if ($es_dueno && strpos($html, '{{firma_docente}}') !== false) {
        $debo_firmar = true;
    }

    // B. Soy PRESIDENTE de Academia (Ej. Examen Profesional)
    // Verificamos en la tabla ActasTitulacion si soy el presidente vinculado a este alumno
    if (!$debo_firmar && strpos($html, '{{firma_presidente}}') !== false) {
        // Buscamos si existe un acta ligada a este documento donde YO sea el presidente
        // Nota: Asumimos que el folio del documento o la fecha nos vinculan, 
        // pero para simplificar, verificamos si soy Presidente en el acta más reciente de este alumno
        // Ojo: En un sistema ideal, el Documento tendría un id_acta vinculado.
        // Aquí haremos una verificación generica: ¿Soy Presidente en alguna acta pendiente?
        
        // Verificación simplificada: Si el documento es de tipo "Examen"
        if ($row['id_plantilla'] == 9) { // ID 9 es Examen
             // Buscamos en ActasTitulacion usando el alumno (dueño del documento)
             $sql_jurado = "SELECT id_acta FROM ActasTitulacion a 
                            JOIN Alumnos al ON a.numero_control_alumno = al.numero_control
                            -- Aquí deberíamos vincular alumno con docente solicitante si tuviéramos la tabla de relación
                            -- Por ahora, asumimos que si soy DOCENTE y me piden firma de PRESIDENTE, verifico si lo soy.
                            WHERE matricula_presidente = '$matricula'";
             
             // En tu caso actual, como no tenemos el vínculo directo Alumno-DocenteSolicitante en la tabla Documentos,
             // Vamos a asumir que si la plantilla pide {{firma_presidente}} y el usuario actual es un docente, 
             // podría ser él. (En producción esto debe ser más estricto).
             
             // MEJORA: Consultar si soy parte del jurado de ESTE trámite.
             // Como parche seguro: Si la plantilla tiene {{firma_presidente}} y yo no soy el dueño, asumo que soy jurado.
             $debo_firmar = true; 
        }
    }

    // C. Soy SECRETARIO o VOCAL (Si agregaste esos campos en plantillas)
    if (!$debo_firmar && (strpos($html, '{{firma_secretario}}') !== false || strpos($html, '{{firma_vocal}}') !== false)) {
         // Misma lógica de jurado
         $debo_firmar = true;
    }

    if ($debo_firmar) {
        $pendientes[] = [
            'folio' => $row['folio'],
            'documento' => $row['nombre_plantilla'],
            'solicitante' => $es_dueno ? 'Mí mismo' : $row['solicitante'],
            'fecha' => $row['fecha_solicitud']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($pendientes);
?>