<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$idEquipo = $_REQUEST['idEquipo'];

$stmt = $conexion->prepare("UPDATE INV_EQUIPO SET ESTADO_AI = 'I' WHERE ID_EQUIPO = ?");
$stmt->bind_param("i", $idEquipo);

if ($stmt->execute()) {
    echo "<script>window.location.href = '../INV_EQUIPO.php';</script>";
} else {
    echo "Error al eliminar: " . $stmt->error;
}

$stmt->close();
?>
