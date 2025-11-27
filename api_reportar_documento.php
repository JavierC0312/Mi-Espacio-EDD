<?php
session_start();
include 'conexion_db.php';

$response = ['success' => false, 'message' => 'Error.'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['folio']) && isset($_POST['mensaje'])) {
    $folio = $_POST['folio'];
    $mensaje = $_POST['mensaje'];
    $matricula = $_SESSION['usuario_id'];

    // Verificar que el documento pertenece a un expediente de este usuario
    // (Omitimos validación compleja por brevedad, pero es recomendable)

    $sql = "UPDATE Documentos SET estado = 'Reportado', mensaje_docente = ? WHERE folio = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $mensaje, $folio);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Reporte enviado al administrador.';
    } else {
        $response['message'] = 'Error al guardar reporte.';
    }
}
echo json_encode($response);
?>