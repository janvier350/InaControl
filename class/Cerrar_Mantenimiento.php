<?php
ob_start();
session_start();
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();

$idMantenimiento  = isset($_POST['idMantenimiento'])  ? (int)$_POST['idMantenimiento']                                    : null;
$fechaEntrega     = isset($_POST['fechaEntrega'])     ? mysqli_real_escape_string($conexion, $_POST['fechaEntrega'])      : null;
$solucionAplicada = isset($_POST['solucionAplicada']) ? mysqli_real_escape_string($conexion, $_POST['solucionAplicada'])  : null;

if (!$idMantenimiento || !$fechaEntrega || !$solucionAplicada) {
    header("Location: ../SCH_Calendar_SOP.php?error=datos_incompletos");
    exit();
}

$verificar = mysqli_prepare($conexion, "SELECT ID_MANTENIMIENTO FROM INV_MANTENIMIENTOS WHERE ID_MANTENIMIENTO = ? AND ESTADO = 'En Reparacion'");
mysqli_stmt_bind_param($verificar, "i", $idMantenimiento);
mysqli_stmt_execute($verificar);
$res = mysqli_stmt_get_result($verificar);

if (mysqli_num_rows($res) === 0) {
    header("Location: ../SCH_Calendar_SOP.php?error=mantenimiento_no_encontrado");
    exit();
}

$stmt = mysqli_prepare($conexion, "UPDATE INV_MANTENIMIENTOS SET FECHA_ENTREGA = ?, SOLUCION_APLICADA = ?, ESTADO = 'Completado' WHERE ID_MANTENIMIENTO = ?");
mysqli_stmt_bind_param($stmt, "ssi", $fechaEntrega, $solucionAplicada, $idMantenimiento);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../SCH_Calendar_SOP.php?success=mantenimiento_cerrado");
} else {
    header("Location: ../SCH_Calendar_SOP.php?error=" . urlencode(mysqli_error($conexion)));
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
exit();
