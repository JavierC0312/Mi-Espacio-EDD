<?php
session_start();
include 'conexion_db.php';

// Limpiar cualquier salida previa (espacios, warnings)
if (ob_get_length()) ob_clean();

if (!isset($_SESSION["loggedin"]) || !isset($_GET['id_expediente'])) {
    die('Acceso denegado o faltan datos.');
}

$id_expediente = $_GET['id_expediente'];
$matricula = $_SESSION['usuario_id'];

try {
    // 1. Verificar propiedad
    $sql_check = "SELECT c.nombre AS nombre_convocatoria FROM Expediente e 
                  JOIN Convocatoria c ON e.id_convocatoria = c.id_convocatoria 
                  WHERE e.id_expediente = ? AND e.matricula_docente = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("is", $id_expediente, $matricula);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows !== 1) {
        die('Error: Expediente no encontrado.');
    }
    
    $exp_data = $result_check->fetch_assoc();
    $zip_filename = 'Expediente_' . preg_replace('/[^a-z0-9_]/i', '_', $exp_data['nombre_convocatoria']) . '.zip';
    
    // 2. Obtener documentos
    $sql_docs = "SELECT nombre_documento_manual, ruta_archivo, folio_documento, d.ruta_pdf 
                 FROM ExpedienteDocumentos ed
                 LEFT JOIN Documentos d ON ed.folio_documento = d.folio
                 WHERE ed.id_expediente = ?";
    
    $stmt_docs = $conn->prepare($sql_docs);
    $stmt_docs->bind_param("i", $id_expediente);
    $stmt_docs->execute();
    $result_docs = $stmt_docs->get_result();

    if ($result_docs->num_rows === 0) die('Expediente vacío.');

    // 3. Crear ZIP
    $zip = new ZipArchive();
    $temp_file = tempnam(sys_get_temp_dir(), 'zip');

    if ($zip->open($temp_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        die('Error al crear ZIP.');
    }

    $base_path = __DIR__ . '/'; 

    while ($row = $result_docs->fetch_assoc()) {
        // Determinar ruta y nombre
        if ($row['ruta_archivo']) {
            // Archivo subido manualmente
            $path = $row['ruta_archivo'];
            $name = $row['nombre_documento_manual'];
        } elseif ($row['ruta_pdf']) {
            // Archivo generado por sistema (PDF Firmado)
            $path = $row['ruta_pdf'];
            $name = $row['folio_documento'];
        } else {
            continue; // No hay archivo físico
        }

        $full_path = $base_path . $path;

        if (file_exists($full_path)) {
            $ext = pathinfo($full_path, PATHINFO_EXTENSION);
            if(!$ext) $ext = 'pdf'; // Default
            
            // Limpiar nombre de archivo
            $clean_name = preg_replace('/[^a-z0-9_\-]/i', '_', $name) . '.' . $ext;
            
            $zip->addFile($full_path, $clean_name);
        }
    }
    $zip->close();

    // 4. Descarga limpia
    if (file_exists($temp_file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($temp_file));
        
        // Limpiar buffer de nuevo por seguridad
        if (ob_get_length()) ob_clean();
        flush();
        
        readfile($temp_file);
        unlink($temp_file);
        exit;
    } else {
        die("Error: No se pudo generar el archivo temporal.");
    }

} catch (Exception $e) {
    die('Error del servidor: ' . $e->getMessage());
}
?>