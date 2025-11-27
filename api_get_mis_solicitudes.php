<?php
session_start();
include 'conexion_db.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    die(json_encode(['error' => 'Acceso denegado']));
}

$matricula = $_SESSION['usuario_id'];
$filtro_estado = $_GET['estado'] ?? 'Todos'; // 'Todos', 'Pendiente', 'Completado'

// Consulta base: Solo las solicitudes DE ESTE docente
$sql = "SELECT 
            d.folio, 
            pt.nombre_plantilla AS nombre_documento,
            d.estado,
            DATE(d.fecha_solicitud) AS fecha,
            d.mensaje_admin -- Traemos el mensaje por si hubo corrección
        FROM Documentos d
        JOIN PlantillasDocumentos pt ON d.id_plantilla = pt.id_plantilla
        WHERE d.matricula_docente_solicitante = ?";

// Filtros opcionales
if ($filtro_estado !== 'Todos') {
    $sql .= " AND d.estado = ?";
}

$sql .= " ORDER BY d.fecha_solicitud DESC";

$stmt = $conn->prepare($sql);

if ($filtro_estado !== 'Todos') {
    $stmt->bind_param("ss", $matricula, $filtro_estado);
} else {
    $stmt->bind_param("s", $matricula);
}

$stmt->execute();
$result = $stmt->get_result();

$solicitudes = [];
while ($row = $result->fetch_assoc()) {
    $solicitudes[] = $row;
}

header('Content-Type: application/json');
echo json_encode($solicitudes);
$stmt->close();
$conn->close();
?>