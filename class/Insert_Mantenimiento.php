<?php
ob_start();
session_start();
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();

$idEquipo       = isset($_POST['idEquipo'])        ? (int)$_POST['idEquipo']                                   : null;
$fechaSalida    = isset($_POST['fechaSalida'])      ? mysqli_real_escape_string($conexion, $_POST['fechaSalida'])    : null;
$danioReportado = isset($_POST['danioReportado'])   ? mysqli_real_escape_string($conexion, $_POST['danioReportado']) : null;

if (!$idEquipo || !$fechaSalida || !$danioReportado) {
    header("Location: ../SCH_Calendar_SOP.php?error=datos_incompletos");
    exit();
}

$stmt = mysqli_prepare($conexion, "INSERT INTO INV_MANTENIMIENTOS (ID_EQUIPO, FECHA_SALIDA, DANIO_REPORTADO, ESTADO) VALUES (?, ?, ?, 'En Reparacion')");
mysqli_stmt_bind_param($stmt, "iss", $idEquipo, $fechaSalida, $danioReportado);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../SCH_Calendar_SOP.php?success=mantenimiento_registrado");
} else {
    header("Location: ../SCH_Calendar_SOP.php?error=" . urlencode(mysqli_error($conexion)));
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
exit();
