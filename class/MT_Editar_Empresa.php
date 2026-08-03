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

$idEmpresa = isset($_POST['idEmpresa']) ? (int)$_POST['idEmpresa'] : null;
$nombre    = isset($_POST['nombre'])    ? mysqli_real_escape_string($con, trim($_POST['nombre']))    : null;
$ruc       = isset($_POST['ruc'])       ? mysqli_real_escape_string($con, trim($_POST['ruc']))       : '';
$email     = isset($_POST['email'])     ? mysqli_real_escape_string($con, trim($_POST['email']))     : '';
$telefono  = isset($_POST['telefono'])  ? mysqli_real_escape_string($con, trim($_POST['telefono']))  : '';
$plan      = isset($_POST['plan'])      ? mysqli_real_escape_string($con, $_POST['plan'])            : 'BASICO';
$estado    = isset($_POST['estado'])    ? mysqli_real_escape_string($con, $_POST['estado'])          : 'A';

$modSoporte    = isset($_POST['mod_soporte'])    ? 1 : 0;
$modInventario = isset($_POST['mod_inventario']) ? 1 : 0;
$modCorreos    = isset($_POST['mod_correos'])    ? 1 : 0;
$modVentas     = isset($_POST['mod_ventas'])     ? 1 : 0;
$modReportes   = isset($_POST['mod_reportes'])   ? 1 : 0;

if (!$idEmpresa || !$nombre) {
    header("Location: ../ADMIN_Empresas.php?error=datos_incompletos");
    exit();
}

$stmtE = mysqli_prepare($con, "UPDATE EMPRESA SET NOMBRE=?, RUC=?, EMAIL_CONTACTO=?, TELEFONO=?, PLAN=?, ESTADO=? WHERE ID_EMPRESA=?");
mysqli_stmt_bind_param($stmtE, "ssssssi", $nombre, $ruc, $email, $telefono, $plan, $estado, $idEmpresa);
mysqli_stmt_execute($stmtE);
mysqli_stmt_close($stmtE);

$stmtC = mysqli_prepare($con, "UPDATE EMPRESA_CONFIG SET MOD_SOPORTE=?, MOD_INVENTARIO=?, MOD_CORREOS=?, MOD_VENTAS=?, MOD_REPORTES=? WHERE ID_EMPRESA=?");
mysqli_stmt_bind_param($stmtC, "iiiiii", $modSoporte, $modInventario, $modCorreos, $modVentas, $modReportes, $idEmpresa);
mysqli_stmt_execute($stmtC);
mysqli_stmt_close($stmtC);

mysqli_close($con);
header("Location: ../ADMIN_Empresas.php?success=empresa_actualizada");
exit();
