<?php
session_start();
// visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'conexion_db.php'; 

// Preparamos una respuesta por defecto
$response = ['success' => false, 'message' => 'Error desconocido.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    try {
        $usuario_form = $_POST["username"]; 
        $contraseña_form = $_POST["password"];

        // 1. Preparar la consulta SQL
        $sql = "SELECT matricula, password_hash, nombre, ap_paterno FROM personal WHERE matricula = ?";
        
        $stmt = $conn->prepare($sql);
        
        // 2. Capturamos el error
        if ($stmt === false) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }

        // 3. Continuar con la ejecución
        $stmt->bind_param("s", $usuario_form);
        
        if ($stmt->execute() === false) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }
        
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($matricula_db, $contrasena_db, $nombre_db, $ap_paterno_db);
            $stmt->fetch();

            // 4. Verificar la contraseña
            if (password_verify($contraseña_form, $contrasena_db)) {
                // Éxito
                $_SESSION["loggedin"] = true;
                $_SESSION["usuario_id"] = $matricula_db;
                $_SESSION["nombre_usuario"] = $nombre_db . " " . $ap_paterno_db;
                $response['success'] = true;
            } else {
                $response['message'] = 'Contraseña incorrecta.';
            }
        } else {
            $response['message'] = 'Usuario (matrícula) no encontrado.';
        }
        
        $stmt->close();
        $conn->close();

    } catch (Exception $e) {
        // 5. Cualquier error (ej. "Tabla 'personal' no existe") será atrapado aquí
        $response['message'] = "Error de Servidor: " . $e->getMessage();
    }

} else {
    $response['message'] = 'Método no permitido.';
}

// 6. Devolver la respuesta
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>