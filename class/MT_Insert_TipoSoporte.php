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

if (!$soporte) {
    header("Location: ../MT_TiposSoporte.php?error=" . urlencode("El nombre del servicio es requerido."));
    exit();
}

$stmt = mysqli_prepare($con, "INSERT INTO COTI_TIPO_SOPORTE (ID_EMPRESA, SOPORTE, DESCRIPCION, ESTADO) VALUES (?, ?, ?, 'A')");
mysqli_stmt_bind_param($stmt, "iss", $idEmpresa, $soporte, $descripcion);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
mysqli_close($con);

header("Location: ../MT_TiposSoporte.php?success=1");
exit();
