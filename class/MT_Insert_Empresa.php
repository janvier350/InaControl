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

$nombre    = isset($_POST['nombre'])    ? mysqli_real_escape_string($con, trim($_POST['nombre']))   : null;
$ruc       = isset($_POST['ruc'])       ? mysqli_real_escape_string($con, trim($_POST['ruc']))      : '';
$email     = isset($_POST['email'])     ? mysqli_real_escape_string($con, trim($_POST['email']))    : '';
$telefono  = isset($_POST['telefono'])  ? mysqli_real_escape_string($con, trim($_POST['telefono'])) : '';
$direccion = isset($_POST['direccion']) ? mysqli_real_escape_string($con, trim($_POST['direccion'])): '';
$plan      = isset($_POST['plan'])      ? mysqli_real_escape_string($con, $_POST['plan'])           : 'BASICO';

// Módulos
$modSoporte    = isset($_POST['mod_soporte'])    ? 1 : 0;
$modInventario = isset($_POST['mod_inventario']) ? 1 : 0;
$modCorreos    = isset($_POST['mod_correos'])    ? 1 : 0;
$modVentas     = isset($_POST['mod_ventas'])     ? 1 : 0;
$modReportes   = isset($_POST['mod_reportes'])   ? 1 : 0;

// Admin de la empresa
$adminNombres   = isset($_POST['admin_nombres'])   ? mysqli_real_escape_string($con, trim($_POST['admin_nombres']))   : null;
$adminApellidos = isset($_POST['admin_apellidos']) ? mysqli_real_escape_string($con, trim($_POST['admin_apellidos'])) : null;
$adminUsuario   = isset($_POST['admin_usuario'])   ? mysqli_real_escape_string($con, trim($_POST['admin_usuario']))   : null;
$adminClave     = isset($_POST['admin_clave'])     ? md5($_POST['admin_clave'])                                       : null;

if (!$nombre || !$adminNombres || !$adminApellidos || !$adminUsuario || !$adminClave) {
    header("Location: ../ADMIN_Empresas.php?error=datos_incompletos");
    exit();
}

// Insertar empresa
$stmtE = mysqli_prepare($con, "INSERT INTO EMPRESA (NOMBRE, RUC, EMAIL_CONTACTO, TELEFONO, DIRECCION, PLAN, ESTADO) VALUES (?,?,?,?,?,?,'A')");
mysqli_stmt_bind_param($stmtE, "ssssss", $nombre, $ruc, $email, $telefono, $direccion, $plan);
if (!mysqli_stmt_execute($stmtE)) {
    header("Location: ../ADMIN_Empresas.php?error=" . urlencode(mysqli_error($con)));
    exit();
}
$idEmpresa = mysqli_insert_id($con);
mysqli_stmt_close($stmtE);

// Insertar configuración de módulos
$stmtC = mysqli_prepare($con, "INSERT INTO EMPRESA_CONFIG (ID_EMPRESA, MOD_SOPORTE, MOD_INVENTARIO, MOD_CORREOS, MOD_VENTAS, MOD_REPORTES) VALUES (?,?,?,?,?,?)");
mysqli_stmt_bind_param($stmtC, "iiiiii", $idEmpresa, $modSoporte, $modInventario, $modCorreos, $modVentas, $modReportes);
mysqli_stmt_execute($stmtC);
mysqli_stmt_close($stmtC);

// Insertar usuario administrador de la empresa (ROL SISTEMA = ID 2)
$rolSistema = 2;
$stmtU = mysqli_prepare($con, "INSERT INTO ADM_USUARIO (ID_EMPRESA, NOMBRES, APELLIDOS, USUARIO, CONTRASENA, IDADM_ROL, ESTADO) VALUES (?,?,?,?,?,?,'A')");
mysqli_stmt_bind_param($stmtU, "issssi", $idEmpresa, $adminNombres, $adminApellidos, $adminUsuario, $adminClave, $rolSistema);
mysqli_stmt_execute($stmtU);
mysqli_stmt_close($stmtU);

mysqli_close($con);
header("Location: ../ADMIN_Empresas.php?success=empresa_creada");
exit();
