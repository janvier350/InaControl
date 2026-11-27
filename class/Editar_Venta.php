<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$idVenta = (int)($_POST['idVenta'] ?? 0);
$fecha = mysqli_real_escape_string($conexion, trim($_POST['fecha'] ?? ''));
$cliente = mysqli_real_escape_string($conexion, trim($_POST['cliente'] ?? ''));
$facturaNo = mysqli_real_escape_string($conexion, trim($_POST['facturaNo'] ?? ''));
$tipo = ($_POST['tipo'] ?? 'CON_FACTURA') === 'SIN_FACTURA' ? 'SIN_FACTURA' : 'CON_FACTURA';
$qq = isset($_POST['qq']) ? (float)$_POST['qq'] : 0;
$precio = isset($_POST['precio']) ? (float)$_POST['precio'] : 0;
$porcRetencion = isset($_POST['porcRetencion']) ? (float)$_POST['porcRetencion'] : 0;
$observacion = mysqli_real_escape_string($conexion, trim($_POST['observacion'] ?? ''));

$redirectQuery = trim($_POST['redirectQuery'] ?? '', '?&');
$destino = "../CONT_Ventas.php" . ($redirectQuery !== '' ? "?$redirectQuery&" : "?");

if (!$idVenta || !$fecha || $qq <= 0 || $precio <= 0) {
    header("Location: {$destino}error=" . urlencode("Datos de venta incompletos."));
    exit();
}

$total = round($qq * $precio, 2);
$valorRetencionExacto = $total * $porcRetencion;
$totalACobrar = round($total - $valorRetencionExacto, 2);
$valorRetencion = round($valorRetencionExacto, 2);

$stmt = mysqli_prepare($conexion, "UPDATE CONT_VENTA SET
    FECHA = ?, CLIENTE = ?, FACTURA_NO = ?, TIPO = ?, QQ = ?, PRECIO = ?, TOTAL = ?,
    PORC_RETENCION = ?, VALOR_RETENCION = ?, TOTAL_A_COBRAR = ?, OBSERVACION = ?
    WHERE ID_VENTA = ?");
mysqli_stmt_bind_param($stmt, "ssssddddddsi",
    $fecha, $cliente, $facturaNo, $tipo, $qq, $precio, $total,
    $porcRetencion, $valorRetencion, $totalACobrar, $observacion, $idVenta);

if (mysqli_stmt_execute($stmt)) {
    header("Location: {$destino}success=1");
} else {
    header("Location: {$destino}error=" . urlencode(mysqli_error($conexion)));
}
mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
