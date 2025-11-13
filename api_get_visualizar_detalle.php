<?php
session_start();
include 'conexion_db.php';

// Verificamos si el usuario ha iniciado sesión y se ha enviado un id_expediente
if (!isset($_SESSION["loggedin"]) || !isset($_GET['id_expediente'])) {
    echo json_encode(['error' => 'Acceso denegado o faltan datos.']);
    exit;
}

// Datos de entrada
$id_expediente = $_GET['id_expediente'];
$matricula = $_SESSION['usuario_id']; // Usamos para seguridad

// Respuesta por defecto
$response = ['error' => 'No se pudo cargar el detalle del expediente.'];

try {
    // 1. Buscamos el detalle del expediente (y confirmamos que es del usuario)
    $sql_exp = "SELECT 
                    e.id_expediente, 
                    c.nombre AS nombre_convocatoria, 
                    p.nombre_periodo 
                FROM Expediente e 
                JOIN Convocatoria c ON e.id_convocatoria = c.id_convocatoria
                JOIN PeriodosEscolares p ON c.id_periodo = p.id_periodo
                WHERE e.id_expediente = ? AND e.matricula_docente = ?
                LIMIT 1";

    $stmt = $conn->prepare($sql_exp);
    $stmt->bind_param("is", $id_expediente, $matricula);
    $stmt->execute();
    $result_exp = $stmt->get_result();

    if ($result_exp && $result_exp->num_rows === 1) {
        $expediente = $result_exp->fetch_assoc();
        
        // 2. Buscamos TODOS sus documentos (con sus nombres y rutas)
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
        
        $documentos_list = [];
        if ($result_docs) {
            while ($row = $result_docs->fetch_assoc()) {
                // Limpiamos los datos para el frontend
                $documentos_list[] = [
                    'nombre' => $row['nombre_documento_manual'] ?? $row['nombre_documento_sistema'] ?? 'Documento sin nombre',
                    'ruta' => $row['ruta_archivo'] ?? $row['ruta_pdf'] // Devolvemos la ruta correcta
                ];
            }
        }
        
        // 3. Preparamos la respuesta final
        $response = [
            'expediente_nombre' => $expediente['nombre_convocatoria'],
            'expediente_periodo' => $expediente['nombre_periodo'],
            'documentos_count' => count($documentos_list),
            'documentos' => $documentos_list
        ];
    } else {
        $response['error'] = 'Expediente no encontrado o no le pertenece.';
    }
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $response['error'] = 'Error de servidor: ' . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>