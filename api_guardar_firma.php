<?php
session_start();
include 'conexion_db.php';

// Ajusta esta ruta si tu carpeta libs está en otro lado
require_once 'libs/phpqrcode/qrlib.php'; 

$response = ['success' => false, 'message' => 'Error desconocido.'];

if (isset($_SESSION["loggedin"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
    
    $matricula = $_SESSION['usuario_id'];
    $firma_base64 = $_POST['firma_base64'];

    // 1. GUARDAR LA IMAGEN DEL DIBUJO (Para que se vea al escanear)
    // Limpiamos el encabezado del base64 (data:image/png;base64,...)
    $img_data = str_replace('data:image/png;base64,', '', $firma_base64);
    $img_data = str_replace(' ', '+', $img_data);
    $data = base64_decode($img_data);

    // Definir rutas
    $folder_dibujos = "archivos/firmas_img/";
    $folder_qrs = "archivos/qr_firmas/";
    
    // Verificar carpetas
    if (!file_exists($folder_dibujos)) mkdir($folder_dibujos, 0777, true);
    if (!file_exists($folder_qrs)) mkdir($folder_qrs, 0777, true);

    // Nombre de archivos
    $file_dibujo = "firma_" . $matricula . ".png";
    $file_qr = "qr_" . $matricula . ".png";

    $path_dibujo = $folder_dibujos . $file_dibujo;
    $path_qr = $folder_qrs . $file_qr;

    // Guardar el dibujo en el servidor
    if (file_put_contents($path_dibujo, $data)) {
        
        // 2. GENERAR EL QR
        // El QR debe contener una URL pública para ver el dibujo.
        // Detectamos la URL base del servidor
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        
        // CAMBIO: Pon aquí tu IPv4 real para que el celular te encuentre
        $host = "192.168.0.2"; // <--- ¡PON TU NÚMERO AQUÍ!
        
        $url_base = "$protocol://$host/Mi_Espacio_EDD/";
        
        $contenido_qr = $url_base . $path_dibujo; // El QR lleva al dibujo

        // Generar QR (Nivel 'L' - Low error correction, tamaño 4, margen 2)
        QRcode::png($contenido_qr, $path_qr, QR_ECLEVEL_L, 4, 2);

        // 3. ACTUALIZAR BASE DE DATOS
        $sql = "UPDATE Personal SET ruta_firma_qr = ? WHERE matricula = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $path_qr, $matricula);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Firma registrada. QR generado en: " . $path_qr;
            $response['qr_url'] = $path_qr;
        } else {
            $response['message'] = "Error al actualizar BD: " . $conn->error;
        }
    } else {
        $response['message'] = "No se pudo guardar la imagen del dibujo.";
    }
} else {
    $response['message'] = "Acceso denegado o datos faltantes.";
}

header('Content-Type: application/json');
echo json_encode($response);
?>