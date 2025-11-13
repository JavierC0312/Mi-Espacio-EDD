<?php
session_start();
include 'conexion_db.php';

// 1. Verificar sesión y que nos hayan pasado un ID
if (!isset($_SESSION["loggedin"]) || !isset($_GET['id_expediente'])) {
    die('Acceso denegado o faltan datos.');
}

$id_expediente = $_GET['id_expediente'];
$matricula = $_SESSION['usuario_id'];

try {
    // 2. (Seguridad) Verificar que el expediente pertenece al usuario
    $sql_check = "SELECT c.nombre AS nombre_convocatoria FROM Expediente e 
                  JOIN Convocatoria c ON e.id_convocatoria = c.id_convocatoria 
                  WHERE e.id_expediente = ? AND e.matricula_docente = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("is", $id_expediente, $matricula);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows !== 1) {
        die('Error: Expediente no encontrado o no le pertenece.');
    }
    
    // Usamos el nombre de la convocatoria para el .zip
    $exp_data = $result_check->fetch_assoc();
    $zip_filename_base = preg_replace('/[^a-z0-9_]/i', '_', $exp_data['nombre_convocatoria']);
    $zip_filename = 'Expediente_' . $zip_filename_base . '.zip';
    
    // 3. Obtener la lista de TODOS los documentos de ese expediente
    $sql_docs = "SELECT 
                    ed.nombre_documento_manual, 
                    ed.ruta_archivo,
                    d.nombre_documento AS nombre_documento_sistema,
                    d.ruta_pdf
                FROM ExpedienteDocumentos ed
                LEFT JOIN Documentos d ON ed.folio_documento = d.folio
                WHERE ed.id_expediente = ?";
    
    $stmt_docs = $conn->prepare($sql_docs);
    $stmt_docs->bind_param("i", $id_expediente);
    $stmt_docs->execute();
    $result_docs = $stmt_docs->get_result();

    if ($result_docs->num_rows === 0) {
        die('Este expediente no tiene documentos para descargar.');
    }

    // 4. Crear el archivo ZIP en una ubicación temporal
    $zip = new ZipArchive();
    $temp_file = tempnam(sys_get_temp_dir(), 'zip'); // Crea un archivo temporal

    if ($zip->open($temp_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        die('No se pudo crear el archivo zip.');
    }

    // 5. Añadir los archivos al ZIP
    // __DIR__ es la ruta de la carpeta raíz (donde está este script)
    $base_path = __DIR__ . '/'; 

    while ($row = $result_docs->fetch_assoc()) {
        $file_name = $row['nombre_documento_manual'] ?? $row['nombre_documento_sistema'] ?? 'documento.pdf';
        $file_path_relative = $row['ruta_archivo'] ?? $row['ruta_pdf'];
        
        // Ruta completa en el servidor
        $file_path_absolute = $base_path . $file_path_relative;

        if ($file_path_relative && file_exists($file_path_absolute)) {
            // Añadimos una extensión .pdf si el nombre no la tiene
            if (pathinfo($file_name, PATHINFO_EXTENSION) == '') {
                $file_name .= '.pdf'; 
            }
            // Añadimos el archivo al zip
            $zip->addFile($file_path_absolute, $file_name);
        }
    }
    $zip->close();

    // 6. Enviar los encabezados HTTP para forzar la descarga
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
    header('Content-Length: ' . filesize($temp_file));
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // 7. Enviar el contenido del archivo zip al navegador
    readfile($temp_file);

    // 8. Borrar el archivo temporal del servidor
    unlink($temp_file);

} catch (Exception $e) {
    die('Error del servidor: ' . $e->getMessage());
}
$conn->close();
exit(); // Detenemos el script
?>