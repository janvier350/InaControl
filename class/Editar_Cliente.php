<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$idCliente = (int)($_POST['idCliente'] ?? 0);
$nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre'] ?? ''));
$estado = ($_POST['estado'] ?? 'A') === 'I' ? 'I' : 'A';

if (!$idCliente || !$nombre) {
    header("Location: ../CONT_Clientes.php?error=" . urlencode("Datos incompletos."));
    exit();
}

$sql = "UPDATE CONT_CLIENTE SET NOMBRE='$nombre', ESTADO='$estado' WHERE ID_CLIENTE=$idCliente";

if ($conexion->query($sql) === TRUE) {
    header("Location: ../CONT_Clientes.php?success=1");
} else {
    header("Location: ../CONT_Clientes.php?error=" . urlencode($conexion->error));
}
$conexion->close();
?>
