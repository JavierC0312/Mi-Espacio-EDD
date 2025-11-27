<?php
session_start();
include 'conexion_db.php';

// Obtener todas las plantillas disponibles para solicitar
$sql = "SELECT id_plantilla, nombre_plantilla FROM PlantillasDocumentos";
$result = $conn->query($sql);

$plantillas = [];
while ($row = $result->fetch_assoc()) {
    $plantillas[] = $row;
}

header('Content-Type: application/json');
echo json_encode($plantillas);
?>