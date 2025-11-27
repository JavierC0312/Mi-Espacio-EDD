<?php
session_start();
include 'conexion_db.php';

$response = ['success' => false, 'message' => 'Error desconocido.'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_plantilla'])) {
    
    $matricula = $_SESSION['usuario_id'];
    $id_plantilla = $_POST['id_plantilla'];
    
    // 1. Generar un Folio Único (Ej: SOL-2025-TIMESTAMP)
    $folio = "SOL-" . date('Y') . "-" . time();

    // 2. Insertar en Documentos (Estado inicial: Pendiente)
    $sql = "INSERT INTO Documentos (folio, nombre_documento, tipo_documento, estado, id_plantilla, matricula_docente_solicitante) 
            SELECT ?, nombre_plantilla, tipo_plantilla, 'Pendiente', ?, ? 
            FROM PlantillasDocumentos WHERE id_plantilla = ?";
            
    $stmt = $conn->prepare($sql);
    // El SELECT interno necesita id_plantilla para copiar el nombre
    $stmt->bind_param("sisi", $folio, $id_plantilla, $matricula, $id_plantilla);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Solicitud creada con éxito. Folio: ' . $folio;
    } else {
        $response['message'] = 'Error al crear solicitud: ' . $conn->error;
    }
    $stmt->close();
}
header('Content-Type: application/json');
echo json_encode($response);
?>