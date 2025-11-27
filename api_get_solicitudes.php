<?php
session_start();
include 'conexion_db.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    die(json_encode(['error' => 'Acceso denegado']));
}

$admin_matricula = $_SESSION['usuario_id'];

// 1. OBTENER ROL Y DEPARTAMENTO DEL USUARIO ACTUAL
$stmt_u = $conn->prepare("SELECT tipo_personal, id_departamento FROM Personal WHERE matricula = ?");
$stmt_u->bind_param("s", $admin_matricula);
$stmt_u->execute();
$user_data = $stmt_u->get_result()->fetch_assoc();
$rol_user = $user_data['tipo_personal'] ?? '';
$depto_user = $user_data['id_departamento'] ?? 0;

if (empty($rol_user)) { die(json_encode(['error' => 'Usuario no válido.'])); }

// 2. PARAMETROS DE FILTRO
$estado_filtro = $_GET['estado'] ?? 'Pendiente'; // 'Pendiente', 'Reportado', etc.
// Si pedimos "Pendiente", también queremos ver los "En Proceso" o "Corregido" que me falten a mí
$estados_busqueda = ($estado_filtro === 'Pendiente') ? "'Pendiente', 'En Proceso', 'Corregido'" : "'$estado_filtro'";

// 3. CONSTRUIR CONSULTA "INTELIGENTE"
// Buscamos documentos donde:
// A. El estado sea válido (Pendiente/En Proceso)
// B. La plantilla tenga el marcador de firma que ME corresponde a MÍ
// C. NO haya firmado yo todavía ese documento específico

$sql = "SELECT 
            d.folio,
            CONCAT(p.nombre, ' ', p.ap_paterno) AS nombre_docente,
            pt.nombre_plantilla AS nombre_documento,
            DATE(d.fecha_solicitud) AS fecha,
            d.estado
        FROM Documentos d
        JOIN Personal p ON d.matricula_docente_solicitante = p.matricula
        JOIN PlantillasDocumentos pt ON d.id_plantilla = pt.id_plantilla
        WHERE d.estado IN ($estados_busqueda)
        
        -- FILTRO: Que yo no haya firmado ya este documento
        AND NOT EXISTS (
            SELECT 1 FROM FirmasDocumento f 
            WHERE f.folio_documento = d.folio AND f.matricula_firmante = '$admin_matricula'
        )

        AND (
            -- LOGICA DE ROLES ESPECIFICA
            
            -- Caso 1: Soy DIRECTOR -> Busco {{firma_director}}
            ('$rol_user' = 'DIRECTOR' AND pt.cuerpo LIKE '%{{firma_director}}%')
            
            OR 
            
            -- Caso 2: Soy SUBDIRECTOR -> Busco {{firma_subdirector}}
            ('$rol_user' = 'SUBDIRECTOR' AND pt.cuerpo LIKE '%{{firma_subdirector}}%')
            
            OR 
            
            -- Caso 3: Soy JEFE DE ÁREA (El más complejo)
            ('$rol_user' = 'JEFE_AREA' AND (
                -- A. Soy Jefe de RH y el doc pide RH
                ($depto_user = 2 AND pt.cuerpo LIKE '%{{firma_jefe_rh}}%')
                OR
                -- B. Soy Jefe de Escolares y el doc pide Escolares
                ($depto_user = 5 AND pt.cuerpo LIKE '%{{firma_jefe_escolares}}%')
                OR
                -- C. Soy Jefe de Desarrollo y el doc pide Desarrollo
                ($depto_user = 6 AND pt.cuerpo LIKE '%{{firma_jefe_desarrollo}}%')
                OR
                -- D. Soy el Jefe ACADÉMICO del docente (Mismo departamento)
                -- Y el documento pide la firma genérica de jefe {{firma_jefe}}
                (p.id_departamento = $depto_user AND pt.cuerpo LIKE '%{{firma_jefe}}%')
            ))
        )";

// 4. FILTROS EXTRA (Búsqueda y Orden)
$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $s = $conn->real_escape_string($search);
    $sql .= " AND (p.nombre LIKE '%$s%' OR p.ap_paterno LIKE '%$s%')";
}

$sort = $_GET['sort'] ?? 'fecha';
$order = $_GET['order'] ?? 'ASC';
$valid_sorts = ['folio', 'nombre_docente', 'nombre_documento', 'fecha'];
if (in_array($sort, $valid_sorts)) {
    $sql .= " ORDER BY $sort " . ($order === 'DESC' ? 'DESC' : 'ASC');
}

// Ejecutar
$result = $conn->query($sql);
$solicitudes = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $solicitudes[] = $row;
    }
}
header('Content-Type: application/json');
echo json_encode($solicitudes);
?>