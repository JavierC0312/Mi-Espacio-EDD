<?php
session_start();
include 'conexion_db.php';

// Verificamos si el usuario ha iniciado sesión
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

// Preparamos una respuesta por defecto
$response = ['error' => 'No se encontraron períodos.'];

// Consultamos todos los períodos escolares, ordenados por el más reciente
$sql = "SELECT id_periodo, nombre_periodo FROM periodoscolares ORDER BY fecha_inicio DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $periodos = [];
    while ($row = $result->fetch_assoc()) {
        $periodos[] = $row;
    }
    // Si se encontraron períodos, esta es la respuesta exitosa
    $response = $periodos;
}

$conn->close();

// Devolvemos la respuesta
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>