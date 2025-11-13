<?php
session_start();
include 'conexion_db.php';

// Verificamos si el usuario ha iniciado sesión y se ha enviado un id_expediente
if (!isset($_SESSION["loggedin"]) || !isset($_GET['id_expediente'])) {
    echo json_encode(['error' => 'Acceso denegado o faltan datos.']);
    exit;
}

// Datos de entrada
$id_expediente = $_GET['id_expediente'];
$matricula = $_SESSION['usuario_id']; // Usamos para seguridad

// Respuesta por defecto
$response = ['error' => 'No se pudo cargar el detalle del expediente.'];

// 1. Buscamos el detalle del expediente (y confirmamos que es del usuario)
$sql_exp = "SELECT 
                e.id_expediente, 
                e.estado, 
                c.nombre AS nombre_convocatoria, 
                p.nombre_periodo 
            FROM Expediente e 
            JOIN Convocatoria c ON e.id_convocatoria = c.id_convocatoria
            JOIN PeriodosEscolares p ON c.id_periodo = p.id_periodo
            WHERE e.id_expediente = ? AND e.matricula_docente = ?
            LIMIT 1";

$stmt = $conn->prepare($sql_exp);
$stmt->bind_param("is", $id_expediente, $matricula);
$stmt->execute();
$result_exp = $stmt->get_result();

if ($result_exp && $result_exp->num_rows === 1) {
    $expediente = $result_exp->fetch_assoc();
    
    // 2. Contamos sus documentos
    $sql_docs = "SELECT COUNT(*) AS total_docs FROM ExpedienteDocumentos WHERE id_expediente = ?";
    $stmt_docs = $conn->prepare($sql_docs);
    $stmt_docs->bind_param("i", $id_expediente);
    $stmt_docs->execute();
    $result_docs = $stmt_docs->get_result();
    $doc_count = $result_docs->fetch_assoc()['total_docs'] ?? 0;
    
    // 3. Preparamos la respuesta final
    $response = [
        'nombre_expediente' => $expediente['nombre_convocatoria'] . " (" . $expediente['nombre_periodo'] . ")",
        'convocatoria' => $expediente['nombre_convocatoria'],
        'estatus' => $expediente['estado'],
        'documentos_count' => $doc_count
    ];
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>