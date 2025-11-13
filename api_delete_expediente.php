<?php
session_start();
include 'conexion_db.php';

// Preparamos una respuesta por defecto
$response = ['success' => false, 'message' => 'Acceso denegado o faltan datos.'];

// Función de ayuda para borrar un directorio y todo su contenido
function deleteDirectory($dir) {
    if (!is_dir($dir)) { return false; }
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? deleteDirectory("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

// Verificamos si el usuario ha iniciado sesión y envía un ID de expediente
if (isset($_SESSION["loggedin"]) && isset($_POST['id_expediente'])) {
    
    $id_expediente = $_POST['id_expediente'];
    $matricula = $_SESSION['usuario_id'];

    // Iniciar una transacción para asegurar que todo se borre, o nada se borre
    $conn->begin_transaction();

    try {
        // 1. (SEGURIDAD) Verificamos que el expediente pertenece al usuario logueado
        $sql_check = "SELECT matricula_docente FROM Expediente WHERE id_expediente = ? AND matricula_docente = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("is", $id_expediente, $matricula);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows !== 1) {
            // Si no es 1, el expediente no existe o no es de este usuario
            throw new Exception('Error: No tiene permiso para eliminar este expediente o no existe.');
        }

        // 2. Borramos los documentos asociados (de ExpedienteDocumentos)
        // (Esto es OBLIGATORIO antes de borrar de la tabla Expediente por las FOREIGN KEY)
        $sql_docs = "DELETE FROM ExpedienteDocumentos WHERE id_expediente = ?";
        $stmt_docs = $conn->prepare($sql_docs);
        $stmt_docs->bind_param("i", $id_expediente);
        $stmt_docs->execute();
        $stmt_docs->close();
        
        // 3. Borramos el expediente principal (de Expediente)
        $sql_exp = "DELETE FROM Expediente WHERE id_expediente = ?";
        $stmt_exp = $conn->prepare($sql_exp);
        $stmt_exp->bind_param("i", $id_expediente);
        $stmt_exp->execute();
        $stmt_exp->close();

        // 4. Si todo en la BD salió bien, confirmamos la transacción
        $conn->commit();

        // 5. (FUERA DE LA BD) Borramos la carpeta física del servidor
        $folderPath = 'archivos/uploads_exp/exp_' . $id_expediente;
        if (is_dir($folderPath)) {
            deleteDirectory($folderPath);
        }
        
        $response['success'] = true;
        $response['message'] = 'Expediente eliminado con éxito.';

    } catch (Exception $e) {
        // Si algo falló, revertimos la transacción
        $conn->rollback();
        $response['message'] = 'Error al eliminar: ' . $e->getMessage();
    }
    $conn->close();
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>