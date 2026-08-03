<?php
ob_start();
session_start();
require_once("funciones.php");
require_once("conexionBD_MT.php");
$con = conectarse_MT();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "SUPERADMIN") {
    header("Location: ../ADMIN_Empresas.php?error=acceso_denegado");
    exit();
}

$idEmpresa  = isset($_POST['idEmpresa'])  ? (int)$_POST['idEmpresa']                                          : null;
$nombres    = isset($_POST['nombres'])    ? mysqli_real_escape_string($con, trim($_POST['nombres']))           : null;
$apellidos  = isset($_POST['apellidos'])  ? mysqli_real_escape_string($con, trim($_POST['apellidos']))         : null;
$telefono   = isset($_POST['telefono'])   ? mysqli_real_escape_string($con, trim($_POST['telefono']))          : '';
$usuario    = isset($_POST['usuario'])    ? mysqli_real_escape_string($con, trim($_POST['usuario']))           : null;
$clave      = isset($_POST['clave'])      ? md5($_POST['clave'])                                               : null;
$idRol      = isset($_POST['idRol'])      ? (int)$_POST['idRol']                                              : 2;

if (!$idEmpresa || !$nombres || !$apellidos || !$usuario || !$clave) {
    header("Location: ../ADMIN_Empresas.php?error=datos_incompletos&empresa=$idEmpresa");
    exit();
}

// Verificar usuario único
$chk = mysqli_prepare($con, "SELECT IDADM_USUARIO FROM ADM_USUARIO WHERE USUARIO=? AND ID_EMPRESA=?");
mysqli_stmt_bind_param($chk, "si", $usuario, $idEmpresa);
mysqli_stmt_execute($chk);
mysqli_stmt_store_result($chk);
if (mysqli_stmt_num_rows($chk) > 0) {
    mysqli_stmt_close($chk);
    header("Location: ../ADMIN_Empresas.php?error=usuario_duplicado&empresa=$idEmpresa");
    exit();
}
mysqli_stmt_close($chk);

$stmt = mysqli_prepare($con, "INSERT INTO ADM_USUARIO (ID_EMPRESA, NOMBRES, APELLIDOS, TELEFONO, USUARIO, CONTRASENA, IDADM_ROL, ESTADO) VALUES (?,?,?,?,?,?,?,'A')");
mysqli_stmt_bind_param($stmt, "isssssi", $idEmpresa, $nombres, $apellidos, $telefono, $usuario, $clave, $idRol);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../ADMIN_Empresas.php?success=usuario_creado&empresa=$idEmpresa");
} else {
    header("Location: ../ADMIN_Empresas.php?error=" . urlencode(mysqli_error($con)) . "&empresa=$idEmpresa");
}
mysqli_stmt_close($stmt);
mysqli_close($con);
exit();
