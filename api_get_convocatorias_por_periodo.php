<?php
session_start();
include 'conexion_db.php';

// Verificamos si el usuario ha iniciado sesión y se ha enviado un id_periodo
if (!isset($_SESSION["loggedin"]) || !isset($_GET['id_periodo'])) {
    echo json_encode(['error' => 'Acceso denegado o faltan datos.']);
    exit;
}

$id_periodo = $_GET['id_periodo'];

// Buscamos TODAS las convocatorias para ese período
$sql = "SELECT id_convocatoria, nombre FROM Convocatoria WHERE id_periodo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_periodo);
$stmt->execute();
$result = $stmt->get_result();

$convocatorias = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $convocatorias[] = $row;
    }
}

// Devolvemos la lista de convocatorias (puede estar vacía)
header('Content-Type: application/json');
echo json_encode($convocatorias);
$conn->close();
exit();
?>
