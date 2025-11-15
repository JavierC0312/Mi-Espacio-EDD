<?php
session_start();
include 'conexion_db.php';

// Verificamos si el usuario ha iniciado sesión
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

// 1. Obtener la matrícula y el rol del ADMINISTRADOR que está logueado
$admin_matricula = $_SESSION['usuario_id'];
$stmt_admin = $conn->prepare("SELECT tipo_personal FROM Personal WHERE matricula = ?");
$stmt_admin->bind_param("s", $admin_matricula);
$stmt_admin->execute();
$admin_result = $stmt_admin->get_result();
$admin_rol = $admin_result->fetch_assoc()['tipo_personal'] ?? '';

if (empty($admin_rol)) {
     echo json_encode(['error' => 'Rol de administrador no válido.']);
     exit;
}

// 2. Preparamos la consulta base
$sql = "SELECT 
            d.folio,
            CONCAT(p.nombre, ' ', p.ap_paterno) AS nombre_docente,
            pt.nombre_plantilla AS nombre_documento,
            DATE(d.fecha_solicitud) AS fecha
        FROM Documentos d
        JOIN Personal p ON d.matricula_docente_solicitante = p.matricula
        JOIN PlantillasDocumentos pt ON d.id_plantilla = pt.id_plantilla
        WHERE 
            d.estado = 'Pendiente' 
            AND pt.rol_firmante = ?"; // El admin solo ve las que él puede firmar

// 3. (Opcional) Añadir filtro de BÚSQUEDA
$search_query = $_GET['search'] ?? '';
$params = [$admin_rol];
$types = "s";

if (!empty($search_query)) {
    $sql .= " AND (p.nombre LIKE ? OR p.ap_paterno LIKE ?)";
    $search_like = '%' . $search_query . '%';
    $params[] = $search_like;
    $params[] = $search_like;
    $types .= "ss"; // Añadimos dos strings
}

// 4. (Opcional) Añadir filtro de ORDEN
$sort_by = $_GET['sort'] ?? 'fecha';
$order = $_GET['order'] ?? 'ASC';
// Lista blanca de columnas seguras para ordenar
$safe_columns = ['folio', 'nombre_docente', 'nombre_documento', 'fecha'];

if (in_array($sort_by, $safe_columns)) {
    $sql .= " ORDER BY $sort_by " . ($order === 'DESC' ? 'DESC' : 'ASC');
}

// 5. Ejecutar la consulta
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$solicitudes = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $solicitudes[] = $row;
    }
}

$conn->close();

// 6. Devolver los resultados en JSON
header('Content-Type: application/json');
echo json_encode($solicitudes);
exit();
?>