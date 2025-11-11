<?php
session_start();
include 'conexion_db.php'; 

// Preparamos una respuesta por defecto
$response = ['error' => 'No se ha iniciado sesión.'];

// Verificamos si el usuario ha iniciado sesión y tenemos su ID (matrícula)
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && isset($_SESSION["usuario_id"])) {
    
    $matricula = $_SESSION["usuario_id"];

    // Preparamos la consulta SQL para obtener todos los datos del perfil
    $sql = "SELECT 
                p.matricula, 
                p.curp, 
                p.nombre, 
                p.ap_paterno, 
                p.ap_materno,
                p.correo, 
                p.fecha_ingreso,
                d.nombre_departamento 
            FROM 
                personal p
            LEFT JOIN 
                departamentos d ON p.id_departamento = d.id_departamento
            WHERE 
                p.matricula = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $matricula);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Si se encontró al usuario, enviamos sus datos
        $response = $result->fetch_assoc();
    } else {
        $response['error'] = 'No se pudo encontrar el perfil del usuario.';
    }
    
    $stmt->close();
    $conn->close();

} 

// Devolvemos la respuesta (datos del usuario o error) en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>