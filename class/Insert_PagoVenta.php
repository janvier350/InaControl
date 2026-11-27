<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$idVenta = (int)($_POST['idVenta'] ?? 0);
$fechaPago = mysqli_real_escape_string($conexion, trim($_POST['fechaPago'] ?? ''));
$formaPago = $_POST['formaPago'] ?? 'DEPOSITO';
$formasValidas = ['EFECTIVO', 'DEPOSITO', 'CHEQUE'];
if (!in_array($formaPago, $formasValidas, true)) { $formaPago = 'DEPOSITO'; }
$monto = isset($_POST['monto']) ? (float)$_POST['monto'] : 0;
$referencia = mysqli_real_escape_string($conexion, trim($_POST['referencia'] ?? ''));

$redirectQuery = trim($_POST['redirectQuery'] ?? '', '?&');
$destino = "../CONT_Ventas.php" . ($redirectQuery !== '' ? "?$redirectQuery&" : "?");

if (!$idVenta || !$fechaPago || $monto <= 0) {
    header("Location: {$destino}error=" . urlencode("Datos de pago incompletos."));
    exit();
}

$stmt = mysqli_prepare($conexion, "INSERT INTO CONT_VENTA_PAGO (ID_VENTA, FECHA_PAGO, FORMA_PAGO, MONTO, REFERENCIA) VALUES (?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "issds", $idVenta, $fechaPago, $formaPago, $monto, $referencia);

if (mysqli_stmt_execute($stmt)) {
    header("Location: {$destino}success=1");
} else {
    header("Location: {$destino}error=" . urlencode(mysqli_error($conexion)));
}
mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
