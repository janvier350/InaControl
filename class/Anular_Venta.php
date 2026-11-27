<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$idVenta = (int)($_POST['idVenta'] ?? 0);
$redirectQuery = trim($_POST['redirectQuery'] ?? '', '?&');
$destino = "../CONT_Ventas.php" . ($redirectQuery !== '' ? "?$redirectQuery&" : "?");

if (!$idVenta) {
    header("Location: {$destino}error=" . urlencode("Venta inválida."));
    exit();
}

$stmt = mysqli_prepare($conexion, "UPDATE CONT_VENTA SET ESTADO = 'I' WHERE ID_VENTA = ?");
mysqli_stmt_bind_param($stmt, "i", $idVenta);

if (mysqli_stmt_execute($stmt)) {
    header("Location: {$destino}success=1");
} else {
    header("Location: {$destino}error=" . urlencode(mysqli_error($conexion)));
}
mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
