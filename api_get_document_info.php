<?php
session_start();
include 'conexion_db.php';

if (!isset($_SESSION["loggedin"]) || !isset($_GET['folio'])) {
    // Manejo de error si no hay sesión o folio
    die(json_encode(['error' => 'Acceso denegado.']));
}

$folio = $_GET['folio'];

// MODIFICACIÓN: Agregamos 'mensaje_docente' a la consulta
$sql = "SELECT d.ruta_pdf, d.estado, d.mensaje_docente FROM Documentos d WHERE d.folio = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $folio);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    
    echo json_encode([
        'ruta_pdf' => $data['ruta_pdf'] ?? null, 
        'estado' => $data['estado'],
        'mensaje_docente' => $data['mensaje_docente'] // Enviamos el mensaje al JS
    ]);
} else {
    echo json_encode(['error' => 'Documento no encontrado.']);
}
?>