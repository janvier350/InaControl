<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$idIncidencia = (int)($_POST['idIncidencia'] ?? 0);
$solucion     = mysqli_real_escape_string($conexion, trim($_POST['solucion'] ?? ''));

if (!$idIncidencia || !$solucion) {
    echo "<script>alert('Datos incompletos.'); history.back();</script>";
    exit();
}

$sql = "UPDATE CCTV_INCIDENCIA SET ESTADO = 'Resuelta', SOLUCION = '$solucion' WHERE ID_INCIDENCIA = $idIncidencia";

if ($conexion->query($sql) === TRUE) {
    echo "<script>alert('Incidencia marcada como resuelta.'); window.location.href = '../CCTV_Incidencias.php';</script>";
} else {
    echo "<script>alert('Error: " . addslashes($conexion->error) . "'); history.back();</script>";
}
$conexion->close();
?>
