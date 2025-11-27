<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'conexion_db.php'; 

$response = ['success' => false, 'message' => 'Error desconocido.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $usuario_form = $_POST["username"]; 
        $contraseña_form = $_POST["password"];

        // AGREGAMOS 'tipo_personal' A LA CONSULTA
        $sql = "SELECT matricula, password_hash, nombre, ap_paterno, tipo_personal FROM personal WHERE matricula = ?";
        
        $stmt = $conn->prepare($sql);
        if ($stmt === false) throw new Exception("Error SQL: " . $conn->error);

        $stmt->bind_param("s", $usuario_form);
        if ($stmt->execute() === false) throw new Exception("Error exec: " . $stmt->error);
        
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($matricula_db, $contrasena_db, $nombre_db, $ap_paterno_db, $tipo_personal_db);
            $stmt->fetch();

            if (password_verify($contraseña_form, $contrasena_db)) {
                $_SESSION["loggedin"] = true;
                $_SESSION["usuario_id"] = $matricula_db;
                $_SESSION["nombre_usuario"] = $nombre_db . " " . $ap_paterno_db;
                
                // GUARDAMOS EL ROL EN LA SESIÓN
                $_SESSION["rol"] = $tipo_personal_db; 

                $response['success'] = true;
                // ENVIAMOS EL ROL AL JAVASCRIPT
                $response['rol'] = $tipo_personal_db; 
            } else {
                $response['message'] = 'Contraseña incorrecta.';
            }
        } else {
            $response['message'] = 'Usuario no encontrado.';
        }
        $stmt->close();
        $conn->close();

    } catch (Exception $e) {
        $response['message'] = "Error: " . $e->getMessage();
    }
} else {
    $response['message'] = 'Método no permitido.';
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>