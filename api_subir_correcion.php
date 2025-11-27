<?php
session_start();
include 'conexion_db.php';

$response = ['success' => false, 'message' => 'Error desconocido.'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['archivo_editado'])) {
    $folio = $_POST['folio'];
    $file = $_FILES['archivo_editado'];
    $mensaje_admin = $_POST['mensaje_admin'] ?? ''; // Recibimos el mensaje
    
    // Validación PDF
    $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $mime = mime_content_type($file["tmp_name"]);

    if ($ext !== 'pdf' || $mime !== 'application/pdf') {
        echo json_encode(['success' => false, 'message' => 'Error: Solo PDF.']);
        exit;
    }

    $target_dir = "archivos/docs_generados/";
    $new_name = "CORREGIDO_" . $folio . ".pdf"; 
    $target_file = $target_dir . $new_name;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        
        // ACTUALIZAMOS: ruta, estado='Corregido' y el mensaje_admin
        $sql = "UPDATE Documentos SET ruta_pdf = ?, estado = 'Corregido', mensaje_admin = ? WHERE folio = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $target_file, $mensaje_admin, $folio);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Corrección guardada.';
            $response['new_path'] = $target_file;
        } else {
            $response['message'] = 'Error BD: ' . $conn->error;
        }
    } else {
        $response['message'] = 'Error al subir archivo.';
    }
}
header('Content-Type: application/json');
echo json_encode($response);
?>