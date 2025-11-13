<?php
session_start();
include 'conexion_db.php';

$response = ['success' => false, 'message' => 'Acceso denegado o faltan datos.'];

if (isset($_SESSION["loggedin"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
    
    $matricula = $_SESSION['usuario_id'];
    $id_convocatoria = $_POST['id_convocatoria'];

    if (empty($id_convocatoria)) {
        $response['message'] = 'Por favor, seleccione una convocatoria.';
    } else {
        try {
            $sql_check = "SELECT id_expediente FROM Expediente WHERE matricula_docente = ? AND id_convocatoria = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("si", $matricula, $id_convocatoria);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                $response['message'] = 'Error: Ya tienes un expediente para esta convocatoria.';
            } else {
                $sql_insert = "INSERT INTO Expediente (matricula_docente, id_convocatoria, estado) VALUES (?, ?, 'En Preparacion')";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("si", $matricula, $id_convocatoria);
                
                if ($stmt_insert->execute()) {

                    // 1. Obtenemos el ID del expediente que acabamos de insertar
                    $newExpedienteId = $conn->insert_id;

                    // 2. Definimos la ruta de la nueva carpeta
                    // (La ruta es relativa a este archivo, que está en la raíz)
                    $folderPath = 'archivos/uploads_exp/exp_' . $newExpedienteId;

                    // 3. Creamos la carpeta si no existe
                    if (!is_dir($folderPath)) {
                        // mkdir(ruta, permisos, recursivo)
                        // 0755 son los permisos estándar (escritura para el dueño)
                        // 'true' permite crear carpetas anidadas si es necesario
                        mkdir($folderPath, 0755, true);
                    }

                    $response['success'] = true;
                    $response['message'] = 'Expediente y carpeta creados con éxito.';

                } else {
                    $response['message'] = 'Error al crear el expediente en la base de datos.';
                }
                $stmt_insert->close();
            }
            $stmt_check->close();
            
        } catch (Exception $e) {
            $response['message'] = 'Error de servidor: ' . $e->getMessage();
        }
    }
    $conn->close();
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>