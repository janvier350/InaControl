<?php
session_start();
require_once("conexionBD_MT.php");
header('Content-Type: application/json');

if (!isset($_SESSION['id_empresa'])) {
    echo json_encode(["success" => false, "message" => "Sesión inválida"]);
    exit();
}

$con = conectarse_MT();
if (!$con) {
    echo json_encode(["success" => false, "message" => "No se pudo conectar a la base de datos"]);
    exit();
}

$idEmpresa    = (int)$_SESSION['id_empresa'];
$id           = (int)($_POST['id'] ?? 0);
$fechaSoporte = trim($_POST['fechaSoporte'] ?? '');
$horaInicio   = trim($_POST['horaInicio'] ?? '');
$horaFin      = trim($_POST['horaFin'] ?? '');
$idUsuario    = (int)($_POST['idUsuario'] ?? 0);
$idSoporte    = (int)($_POST['idSoporte'] ?? 0);
$comentario   = $_POST['comentario'] ?? '';

if (!$id || !$fechaSoporte || !$horaInicio || !$horaFin || !$idUsuario || !$idSoporte) {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit();
}

$stmt = mysqli_prepare($con,
    "UPDATE COTI_CALENDARIO SET
        FECHA_SOPORTE = ?, HORA_INICIO = ?, HORA_FIN = ?, ID_USUARIO = ?, ID_SOPORTE = ?, COMENTARIO = ?
     WHERE ID_CALENDARIO_SOPORTE = ? AND ID_EMPRESA = ?"
);
mysqli_stmt_bind_param($stmt, "sssiisii", $fechaSoporte, $horaInicio, $horaFin, $idUsuario, $idSoporte, $comentario, $id, $idEmpresa);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => mysqli_error($con)]);
}

mysqli_stmt_close($stmt);
mysqli_close($con);
