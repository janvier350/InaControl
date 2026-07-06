<?php
ob_start();
session_start();
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();

$correo         = isset($_POST['correo'])         ? mysqli_real_escape_string($conexion, trim($_POST['correo']))         : null;
$contrasena     = isset($_POST['contrasena'])     ? mysqli_real_escape_string($conexion, $_POST['contrasena'])           : null;
$departamento   = isset($_POST['departamento'])   ? mysqli_real_escape_string($conexion, trim($_POST['departamento']))   : '';
$almacenamiento = isset($_POST['almacenamiento']) ? mysqli_real_escape_string($conexion, trim($_POST['almacenamiento'])) : '';
$idUsuario      = isset($_POST['idUsuario']) && $_POST['idUsuario'] !== '' ? (int)$_POST['idUsuario'] : null;

if (!$correo || !$contrasena) {
    header("Location: ../COR_Correos.php?error=datos_incompletos");
    exit();
}

if ($idUsuario) {
    $stmt = mysqli_prepare($conexion, "INSERT INTO COR_CORREO (CORREO, CONTRASENA, DEPARTAMENTO, ALMACENAMIENTO, IDADM_USUARIO, ESTADO) VALUES (?, ?, ?, ?, ?, 'A')");
    mysqli_stmt_bind_param($stmt, "ssssi", $correo, $contrasena, $departamento, $almacenamiento, $idUsuario);
} else {
    $stmt = mysqli_prepare($conexion, "INSERT INTO COR_CORREO (CORREO, CONTRASENA, DEPARTAMENTO, ALMACENAMIENTO, ESTADO) VALUES (?, ?, ?, ?, 'A')");
    mysqli_stmt_bind_param($stmt, "ssss", $correo, $contrasena, $departamento, $almacenamiento);
}

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../COR_Correos.php?success=correo_registrado");
} else {
    header("Location: ../COR_Correos.php?error=" . urlencode(mysqli_error($conexion)));
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
exit();
