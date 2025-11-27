<?php
session_start();
include 'conexion_db.php';

$response = ['error' => 'No se ha iniciado sesión.'];

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $matricula = $_SESSION["usuario_id"];

    // MODIFICACIÓN: Agregamos 'p.ruta_firma_qr' a la consulta
    $sql = "SELECT 
                p.matricula, p.curp, p.nombre, p.ap_paterno, p.ap_materno, 
                p.correo, p.fecha_ingreso, p.tipo_personal, p.ruta_firma_qr,
                d.nombre_departamento 
            FROM personal p
            LEFT JOIN departamentos d ON p.id_departamento = d.id_departamento
            WHERE p.matricula = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $matricula);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $response = $result->fetch_assoc();
    } else {
        $response['error'] = 'Usuario no encontrado.';
    }
    $stmt->close();
    $conn->close();
} 

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>