<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

header('Content-Type: application/json');

$id            = (int)($_POST['id'] ?? 0);
$fechaSoporte  = $conexion->real_escape_string($_POST['fechaSoporte'] ?? '');
$horaInicio    = $conexion->real_escape_string($_POST['horaInicio'] ?? '');
$horaFin       = $conexion->real_escape_string($_POST['horaFin'] ?? '');
$idUsuario     = (int)($_POST['idUsuario'] ?? 0);
$idSoporte     = (int)($_POST['idSoporte'] ?? 0);
$comentario    = $_POST['comentario'] ?? '';

if (!$id || !$fechaSoporte || !$horaInicio || !$horaFin || !$idUsuario || !$idSoporte) {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit();
}

// Evidencias actuales guardadas en BD
$stmtGet = $conexion->prepare("SELECT EVIDENCIAS FROM COTI_CALENDARIO WHERE ID_CALENDARIO_SOPORTE = ?");
$stmtGet->bind_param("i", $id);
$stmtGet->execute();
$rowActual = $stmtGet->get_result()->fetch_assoc();
$stmtGet->close();

$evidenciasActuales = !empty($rowActual['EVIDENCIAS']) ? explode(',', $rowActual['EVIDENCIAS']) : [];

// Rutas a eliminar (solo si realmente pertenecen a este registro)
$evidenciasEliminar = [];
if (!empty($_POST['evidenciasEliminar'])) {
    $decoded = json_decode($_POST['evidenciasEliminar'], true);
    if (is_array($decoded)) {
        $evidenciasEliminar = $decoded;
    }
}

$evidenciasFinal = [];
foreach ($evidenciasActuales as $ruta) {
    if (in_array($ruta, $evidenciasEliminar, true)) {
        $rutaAbsoluta = __DIR__ . '/../' . $ruta;
        if (strpos($ruta, 'images/evidencias/') === 0 && file_exists($rutaAbsoluta)) {
            @unlink($rutaAbsoluta);
        }
    } else {
        $evidenciasFinal[] = $ruta;
    }
}

// Nuevas imágenes subidas
if (!empty($_FILES['evidenciasNuevas']) && is_array($_FILES['evidenciasNuevas']['name'])) {
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'xls', 'xlsx'];
    $totalArchivos = count($_FILES['evidenciasNuevas']['name']);

    for ($i = 0; $i < $totalArchivos; $i++) {
        if ($_FILES['evidenciasNuevas']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        $extension = strtolower(pathinfo($_FILES['evidenciasNuevas']['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensionesPermitidas)) {
            continue;
        }
        $nombreArchivo = 'soporte_' . $id . '_' . time() . '_' . $i . '.' . $extension;
        $rutaDestino = __DIR__ . '/../images/evidencias/' . $nombreArchivo;

        if (move_uploaded_file($_FILES['evidenciasNuevas']['tmp_name'][$i], $rutaDestino)) {
            $evidenciasFinal[] = 'images/evidencias/' . $nombreArchivo;
        }
    }
}

$evidenciasGuardar = implode(',', $evidenciasFinal);

$stmt = $conexion->prepare(
    "UPDATE COTI_CALENDARIO SET
        FECHA_SOPORTE = ?,
        HORA_INICIO = ?,
        HORA_FIN = ?,
        ID_USUARIO = ?,
        ID_SOPORTE = ?,
        COMENTARIO = ?,
        EVIDENCIAS = ?
     WHERE ID_CALENDARIO_SOPORTE = ?"
);
$stmt->bind_param("sssiissi", $fechaSoporte, $horaInicio, $horaFin, $idUsuario, $idSoporte, $comentario, $evidenciasGuardar, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => $stmt->error]);
}

$stmt->close();
$conexion->close();
?>
