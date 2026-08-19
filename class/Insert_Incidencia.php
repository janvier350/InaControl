<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$equipo      = trim($_POST['equipo'] ?? '');
$tipo        = mysqli_real_escape_string($conexion, trim($_POST['tipo'] ?? 'Falla'));
$fecha       = mysqli_real_escape_string($conexion, trim($_POST['fecha'] ?? ''));
$descripcion = mysqli_real_escape_string($conexion, trim($_POST['descripcion'] ?? ''));

if (!$equipo || !$fecha || !$descripcion) {
    echo "<script>alert('Datos incompletos.'); history.back();</script>";
    exit();
}

$idCamara = 'NULL';
$idDvr = 'NULL';

if (strpos($equipo, 'camara-') === 0) {
    $idCamara = (int) substr($equipo, 7);
} elseif (strpos($equipo, 'dvr-') === 0) {
    $idDvr = (int) substr($equipo, 4);
} else {
    echo "<script>alert('Equipo inválido.'); history.back();</script>";
    exit();
}

$sql = "INSERT INTO CCTV_INCIDENCIA (ID_CAMARA, ID_DVR, TIPO, FECHA, DESCRIPCION, ESTADO)
        VALUES ($idCamara, $idDvr, '$tipo', '$fecha', '$descripcion', 'Abierta')";

if ($conexion->query($sql) === TRUE) {
    echo "<script>alert('Incidencia registrada correctamente.'); window.location.href = '../CCTV_Incidencias.php';</script>";
} else {
    echo "<script>alert('Error al registrar: " . addslashes($conexion->error) . "'); history.back();</script>";
}
$conexion->close();
?>
