<?php
session_start();
include 'conexion_db.php';

// Verificamos si el usuario ha iniciado sesión y se ha enviado un id_periodo
if (!isset($_SESSION["loggedin"]) || !isset($_GET['id_periodo'])) {
    echo json_encode(['error' => 'Acceso denegado o faltan datos.']);
    exit;
}

// Datos de entrada
$id_periodo = $_GET['id_periodo'];
$matricula = $_SESSION['usuario_id'];

// Respuesta por defecto
$response = ['error' => 'No se encontró un expediente para este período.'];

// 1. Buscamos el expediente principal
$sql_exp = "SELECT 
                e.id_expediente, 
                e.estatus, 
                c.nombre AS nombre_convocatoria, 
                p.nombre_periodo 
            FROM expediente e 
            JOIN convocatoria c ON e.id_convocatoria = c.id_convocatoria
            JOIN periodoscolares p ON c.id_periodo_ini = p.id_periodo
            WHERE e.matricula_docente = ? AND p.id_periodo = ?
            LIMIT 1";

$stmt = $conn->prepare($sql_exp);
$stmt->bind_param("si", $matricula, $id_periodo);
$stmt->execute();
$result_exp = $stmt->get_result();

if ($result_exp && $result_exp->num_rows === 1) {
    
    $expediente = $result_exp->fetch_assoc();
    $id_expediente = $expediente['id_expediente'];

    // 2. Buscamos los documentos de ESE expediente
    $sql_docs = "SELECT 
                    ed.id_exp_doc, 
                    ed.nombre_documento_manual, 
                    pt.tipo_archivo 
                FROM expedientedocumentos ed
                JOIN plantilladocument pt ON ed.id_plantilla_documento = pt.id_plantilla_documento
                WHERE ed.id_expediente = ?";
                
    $stmt_docs = $conn->prepare($sql_docs);
    $stmt_docs->bind_param("i", $id_expediente);
    $stmt_docs->execute();
    $result_docs = $stmt_docs->get_result();
    
    $documentos = [];
    if ($result_docs) {
        while ($row_doc = $result_docs->fetch_assoc()) {
            $documentos[] = $row_doc;
        }
    }
    
    $stmt_docs->close();

    // 3. Preparamos la respuesta final
    $response = [
        'id_expediente' => $id_expediente,
        'nombre_expediente' => $expediente['nombre_convocatoria'] . " (" . $expediente['nombre_periodo'] . ")",
        'convocatoria' => $expediente['nombre_convocatoria'],
        'estatus' => $expediente['estatus'] ?? 'No definido',
        'documentos_count' => count($documentos),
        'documentos_list' => $documentos
    ];
}

$stmt->close();
$conn->close();

// Devolvemos la respuesta
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>