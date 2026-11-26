<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre'] ?? ''));

if ($nombre === '') {
    header("Location: ../CONT_Clientes.php?error=" . urlencode("El nombre es obligatorio."));
    exit();
}

$sql = "INSERT INTO CONT_CLIENTE (NOMBRE) VALUES ('$nombre')";

if ($conexion->query($sql) === TRUE) {
    header("Location: ../CONT_Clientes.php?success=1");
} else {
    header("Location: ../CONT_Clientes.php?error=" . urlencode($conexion->error));
}
$conexion->close();
?>
