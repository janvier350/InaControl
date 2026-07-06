<?php
ob_start();
session_start();
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();

$idCorreo       = isset($_POST['idCorreo'])       ? (int)$_POST['idCorreo']                                              : null;
$correo         = isset($_POST['correo'])         ? mysqli_real_escape_string($conexion, trim($_POST['correo']))         : null;
$contrasena     = isset($_POST['contrasena'])     ? mysqli_real_escape_string($conexion, $_POST['contrasena'])           : null;
$departamento   = isset($_POST['departamento'])   ? mysqli_real_escape_string($conexion, trim($_POST['departamento']))   : '';
$almacenamiento = isset($_POST['almacenamiento']) ? mysqli_real_escape_string($conexion, trim($_POST['almacenamiento'])) : '';
$idUsuario      = isset($_POST['idUsuario']) && $_POST['idUsuario'] !== '' ? (int)$_POST['idUsuario'] : null;
$estado         = isset($_POST['estado'])         ? mysqli_real_escape_string($conexion, $_POST['estado'])               : 'A';

if (!$idCorreo || !$correo || !$contrasena) {
    header("Location: ../COR_Correos.php?error=datos_incompletos");
    exit();
}

if ($idUsuario) {
    $stmt = mysqli_prepare($conexion, "UPDATE COR_CORREO SET CORREO=?, CONTRASENA=?, DEPARTAMENTO=?, ALMACENAMIENTO=?, IDADM_USUARIO=?, ESTADO=? WHERE ID_CORREO=?");
    mysqli_stmt_bind_param($stmt, "ssssssi", $correo, $contrasena, $departamento, $almacenamiento, $idUsuario, $estado, $idCorreo);
} else {
    $stmt = mysqli_prepare($conexion, "UPDATE COR_CORREO SET CORREO=?, CONTRASENA=?, DEPARTAMENTO=?, ALMACENAMIENTO=?, IDADM_USUARIO=NULL, ESTADO=? WHERE ID_CORREO=?");
    mysqli_stmt_bind_param($stmt, "sssssi", $correo, $contrasena, $departamento, $almacenamiento, $estado, $idCorreo);
}

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../COR_Correos.php?success=correo_actualizado");
} else {
    header("Location: ../COR_Correos.php?error=" . urlencode(mysqli_error($conexion)));
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
exit();
