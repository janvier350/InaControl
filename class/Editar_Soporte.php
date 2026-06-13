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

$stmt = $conexion->prepare(
    "UPDATE COTI_CALENDARIO SET
        FECHA_SOPORTE = ?,
        HORA_INICIO = ?,
        HORA_FIN = ?,
        ID_USUARIO = ?,
        ID_SOPORTE = ?,
        COMENTARIO = ?
     WHERE ID_CALENDARIO_SOPORTE = ?"
);
$stmt->bind_param("sssiisi", $fechaSoporte, $horaInicio, $horaFin, $idUsuario, $idSoporte, $comentario, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => $stmt->error]);
}

$stmt->close();
$conexion->close();
?>
