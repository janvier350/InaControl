<?php
session_start();
require_once("conexionBD_MT.php");

if (!isset($_SESSION['id_empresa'])) {
    header("Location: ../EMPRESA_login.php?error=" . urlencode("Sesión inválida."));
    exit();
}

$con = conectarse_MT();
if (!$con) {
    header("Location: ../MT_TiposSoporte.php?error=" . urlencode("No se pudo conectar a la base de datos."));
    exit();
}

$idEmpresa   = (int)$_SESSION['id_empresa'];
$soporte     = trim($_POST['soporte'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

$esAjax = !empty($_POST['ajax']);

if (!$soporte) {
    if ($esAjax) { header("Content-Type: application/json"); echo json_encode(["success"=>false, "message"=>"El nombre del servicio es requerido."]); exit(); }
    header("Location: ../MT_TiposSoporte.php?error=" . urlencode("El nombre del servicio es requerido."));
    exit();
}

$stmt = mysqli_prepare($con, "INSERT INTO COTI_TIPO_SOPORTE (ID_EMPRESA, SOPORTE, DESCRIPCION, ESTADO) VALUES (?, ?, ?, 'A')");
mysqli_stmt_bind_param($stmt, "iss", $idEmpresa, $soporte, $descripcion);
$ok = mysqli_stmt_execute($stmt);
$idNuevo = mysqli_insert_id($con);
mysqli_stmt_close($stmt);
mysqli_close($con);

if ($esAjax) {
    header("Content-Type: application/json");
    echo json_encode(["success" => $ok, "id" => $idNuevo, "soporte" => $soporte]);
    exit();
}

header("Location: ../MT_TiposSoporte.php?success=1");
exit();
