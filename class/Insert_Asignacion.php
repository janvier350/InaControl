<?php
ob_start();
session_start();
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();

$idEquipo         = isset($_POST['idEquipo'])         ? (int)$_POST['idEquipo']                                  : null;
$idUsuario        = isset($_POST['idUsuario'])        ? (int)$_POST['idUsuario']                                 : null;
$fechaAsignacion  = isset($_POST['fechaAsignacion'])  ? mysqli_real_escape_string($conexion, $_POST['fechaAsignacion']) : null;

if (!$idEquipo || !$idUsuario || !$fechaAsignacion) {
    header("Location: ../INV_EQUIPO.php?error=datos_incompletos");
    exit();
}

$cerrar = mysqli_prepare($conexion, "UPDATE INV_ASIGNACION SET ESTADO = 'I', FECHA_DEVOLUCION = ? WHERE ID_EQUIPO = ? AND ESTADO = 'A'");
mysqli_stmt_bind_param($cerrar, "si", $fechaAsignacion, $idEquipo);
mysqli_stmt_execute($cerrar);
mysqli_stmt_close($cerrar);

$stmt = mysqli_prepare($conexion, "INSERT INTO INV_ASIGNACION (ID_EQUIPO, FECHA_ASIGNACION, ID_ADM_USUARIO, ESTADO, FECHA_DEVOLUCION) VALUES (?, ?, ?, 'A', '0000-00-00')");
mysqli_stmt_bind_param($stmt, "isi", $idEquipo, $fechaAsignacion, $idUsuario);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../INV_EQUIPO.php?success=equipo_asignado");
} else {
    header("Location: ../INV_EQUIPO.php?error=" . urlencode(mysqli_error($conexion)));
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
exit();
