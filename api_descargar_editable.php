<?php
session_start();
include 'conexion_db.php';

if (!isset($_SESSION["loggedin"]) || !isset($_GET['folio'])) { die("Error."); }

$folio = $_GET['folio'];

// (Reutilizamos la lógica de obtención de datos para llenar la plantilla)
// ... [Para abreviar, asume la misma consulta SQL y reemplazo de variables que arriba] ...
// En un entorno real, harías una función compartida para no repetir código.
// POR AHORA, COPIA Y PEGA LA LÓGICA DEL ARCHIVO ANTERIOR AQUÍ (líneas 10 a 48)
// ---------------------------------------------------------
$sql = "SELECT d.folio, p.nombre, p.ap_paterno, p.ap_materno, p.matricula, p.curp, dept.nombre_departamento, pt.cuerpo AS plantilla_html FROM Documentos d JOIN Personal p ON d.matricula_docente_solicitante = p.matricula JOIN Departamentos dept ON p.id_departamento = dept.id_departamento JOIN PlantillasDocumentos pt ON d.id_plantilla = pt.id_plantilla WHERE d.folio = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $folio);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();
$html = $data['plantilla_html'];
$nombre = $data['nombre'] . ' ' . $data['ap_paterno'] . ' ' . $data['ap_materno'];
$html = str_replace('{{nombre_completo}}', $nombre, $html);
$html = str_replace('{{matricula}}', $data['matricula'], $html);
// ---------------------------------------------------------

// FORZAR DESCARGA COMO WORD
header("Content-type: application/vnd.ms-word");
header("Content-Disposition: attachment;Filename=Editable_$folio.doc");

echo "<html>";
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">";
echo "<body>";
echo $html;
echo "</body>";
echo "</html>";
exit;
?>