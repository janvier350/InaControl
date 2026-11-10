<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$idProducto = (int)($_POST['idProducto'] ?? 0);
$codigo = mysqli_real_escape_string($conexion, trim($_POST['codigo'] ?? ''));
$nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre'] ?? ''));
$estado = ($_POST['estado'] ?? 'A') === 'I' ? 'I' : 'A';

if (!$idProducto || !$nombre) {
    header("Location: ../CONT_Productos.php?error=" . urlencode("Datos incompletos."));
    exit();
}

$codigoSql = $codigo !== '' ? "'$codigo'" : 'NULL';
$sql = "UPDATE CONT_PRODUCTO SET CODIGO=$codigoSql, NOMBRE='$nombre', ESTADO='$estado' WHERE ID_PRODUCTO=$idProducto";

if ($conexion->query($sql) === TRUE) {
    header("Location: ../CONT_Productos.php?success=1");
} else {
    header("Location: ../CONT_Productos.php?error=" . urlencode($conexion->error));
}
$conexion->close();
?>
