<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$fecha       = mysqli_real_escape_string($conexion, trim($_POST['fecha'] ?? ''));
$idProducto  = (int)($_POST['idProducto'] ?? 0);
$cliente     = mysqli_real_escape_string($conexion, trim($_POST['cliente'] ?? ''));
$tipo        = ($_POST['tipo'] ?? 'CON_FACTURA') === 'SIN_FACTURA' ? 'SIN_FACTURA' : 'CON_FACTURA';
$qq          = isset($_POST['qq']) ? (float)$_POST['qq'] : 0;
$precio      = isset($_POST['precio']) ? (float)$_POST['precio'] : 0;
$observacion = mysqli_real_escape_string($conexion, trim($_POST['observacion'] ?? ''));

if (!$fecha || !$idProducto || $qq <= 0 || $precio <= 0) {
    header("Location: ../CONT_Ventas.php?error=" . urlencode("Datos incompletos."));
    exit();
}

$total = round($qq * $precio, 2);

$stmt = mysqli_prepare($conexion, "INSERT INTO CONT_VENTA (FECHA, ID_PRODUCTO, CLIENTE, TIPO, QQ, PRECIO, TOTAL, OBSERVACION, ESTADO) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'A')");
mysqli_stmt_bind_param($stmt, "sissddds", $fecha, $idProducto, $cliente, $tipo, $qq, $precio, $total, $observacion);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../CONT_Ventas.php?success=1");
} else {
    header("Location: ../CONT_Ventas.php?error=" . urlencode(mysqli_error($conexion)));
}
mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
