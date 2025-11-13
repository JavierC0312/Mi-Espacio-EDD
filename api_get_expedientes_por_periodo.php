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
$response = ['error' => 'No se encontraron expedientes para este período.'];

// Buscamos TODOS los expedientes del usuario para ese período
$sql = "SELECT 
            e.id_expediente, 
            c.nombre AS nombre_convocatoria, 
            p.nombre_periodo 
        FROM Expediente e 
        JOIN Convocatoria c ON e.id_convocatoria = c.id_convocatoria
        JOIN PeriodosEscolares p ON c.id_periodo = p.id_periodo
        WHERE e.matricula_docente = ? AND p.id_periodo = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $matricula, $id_periodo);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $expedientes = [];
    while ($row = $result->fetch_assoc()) {
        $expedientes[] = $row;
    }
    // Si se encontraron, esta es la respuesta exitosa
    $response = $expedientes; 
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>