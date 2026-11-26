<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$nombre = trim($_POST['nombre'] ?? '');
$destino = ($_POST['origen'] ?? '') === 'ventas' ? '../CONT_Ventas.php' : '../CONT_Clientes.php';

if ($nombre === '') {
    header("Location: $destino?error=" . urlencode("El nombre es obligatorio."));
    exit();
}

$stmtChk = mysqli_prepare($conexion, "SELECT ID_CLIENTE FROM CONT_CLIENTE WHERE NOMBRE = ?");
mysqli_stmt_bind_param($stmtChk, "s", $nombre);
mysqli_stmt_execute($stmtChk);
if (mysqli_stmt_get_result($stmtChk)->fetch_assoc()) {
    header("Location: $destino?success=1&clienteNuevo=" . urlencode($nombre));
    exit();
}

$stmtIns = mysqli_prepare($conexion, "INSERT INTO CONT_CLIENTE (NOMBRE) VALUES (?)");
mysqli_stmt_bind_param($stmtIns, "s", $nombre);

if (mysqli_stmt_execute($stmtIns)) {
    header("Location: $destino?success=1&clienteNuevo=" . urlencode($nombre));
} else {
    header("Location: $destino?error=" . urlencode($conexion->error));
}
$conexion->close();
?>
