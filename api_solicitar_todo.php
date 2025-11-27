<?php
session_start();
include 'conexion_db.php';

$response = ['success' => false, 'message' => 'Error.'];

if (isset($_SESSION["loggedin"])) {
    $matricula = $_SESSION['usuario_id'];
    
    // 1. Obtener TODAS las plantillas disponibles
    $sql_plantillas = "SELECT id_plantilla, nombre_plantilla, tipo_plantilla FROM PlantillasDocumentos";
    $result = $conn->query($sql_plantillas);
    
    $count = 0;
    
    if ($result) {
        $stmt = $conn->prepare("INSERT INTO Documentos (folio, nombre_documento, tipo_documento, estado, id_plantilla, matricula_docente_solicitante) VALUES (?, ?, ?, 'Pendiente', ?, ?)");
        
        while ($row = $result->fetch_assoc()) {
            $folio = "SOL-" . date('Y') . "-" . time() . "-" . $row['id_plantilla'];
            $stmt->bind_param("sssis", $folio, $row['nombre_plantilla'], $row['tipo_plantilla'], $row['id_plantilla'], $matricula);
            if($stmt->execute()) {
                $count++;
            }
        }
        $stmt->close();
    }
    
    $response['success'] = true;
    $response['message'] = "Se generaron $count solicitudes exitosamente.";
}
header('Content-Type: application/json');
echo json_encode($response);
?>