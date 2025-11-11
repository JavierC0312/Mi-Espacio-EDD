<?php
session_start();
include 'conexion_db.php'; 

// Preparamos una respuesta por defecto
$response = ['success' => false, 'message' => 'No se ha iniciado sesión.'];

// Verificamos si el usuario ha iniciado sesión
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && isset($_SESSION["usuario_id"])) {
    
    // Obtenemos la matrícula de la sesión
    $matricula = $_SESSION["usuario_id"];

    // Obtenemos los datos enviados por el formulario (POST)
    $nombre = $_POST['nombre'];
    $ap_paterno = $_POST['ap_paterno'];
    $ap_materno = $_POST['ap_materno'];
    $correo = $_POST['correo'];
    $curp = $_POST['curp'];

    // consulta preparada
    $sql = "UPDATE personal SET 
                nombre = ?, 
                ap_paterno = ?, 
                ap_materno = ?, 
                correo = ?, 
                curp = ? 
            WHERE 
                matricula = ?";

    $stmt = $conn->prepare($sql);
    
    // Vinculamos los parámetros
    $stmt->bind_param("ssssss", 
        $nombre, 
        $ap_paterno, 
        $ap_materno, 
        $correo, 
        $curp, 
        $matricula
    );

    // Ejecutamos la consulta
    if ($stmt->execute()) {
        // Verificamos si realmente se cambió algo
        if ($stmt->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = 'Perfil actualizado con éxito.';
            
            // Actualizamos el nombre en la sesión, por si acaso
            $_SESSION["nombre_usuario"] = $nombre . " " . $ap_paterno;
        } else {
            $response['message'] = 'No se realizaron cambios (los datos pueden ser los mismos).';
            $response['success'] = true; // Técnicamente no es un error
        }
    } else {
        $response['message'] = 'Error al actualizar el perfil: ' . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();

} else {
    $response['message'] = 'Acceso denegado.';
}

// Devolvemos la respuesta
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>