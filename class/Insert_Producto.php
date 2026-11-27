<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$codigo = mysqli_real_escape_string($conexion, trim($_POST['codigo'] ?? ''));
$nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre'] ?? ''));

if (!$nombre) {
    header("Location: ../CONT_Productos.php?error=" . urlencode("El nombre es obligatorio."));
    exit();
}

$codigoSql = $codigo !== '' ? "'$codigo'" : 'NULL';
$sql = "INSERT INTO CONT_PRODUCTO (CODIGO, NOMBRE, ESTADO) VALUES ($codigoSql, '$nombre', 'A')";

if ($conexion->query($sql) === TRUE) {
    header("Location: ../CONT_Productos.php?success=1");
} else {
    header("Location: ../CONT_Productos.php?error=" . urlencode($conexion->error));
}
$conexion->close();
?>
