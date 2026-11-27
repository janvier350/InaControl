<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$fecha          = mysqli_real_escape_string($conexion, trim($_POST['fecha'] ?? ''));
$idProducto     = (int)($_POST['idProducto'] ?? 0);
$cliente        = mysqli_real_escape_string($conexion, trim($_POST['cliente'] ?? ''));
$facturaNo      = mysqli_real_escape_string($conexion, trim($_POST['facturaNo'] ?? ''));
$tipo           = ($_POST['tipo'] ?? 'CON_FACTURA') === 'SIN_FACTURA' ? 'SIN_FACTURA' : 'CON_FACTURA';
$qq             = isset($_POST['qq']) ? (float)$_POST['qq'] : 0;
$precio         = isset($_POST['precio']) ? (float)$_POST['precio'] : 0;
$porcRetencion  = isset($_POST['porcRetencion']) ? (float)$_POST['porcRetencion'] : 0;
$observacion    = mysqli_real_escape_string($conexion, trim($_POST['observacion'] ?? ''));

if (!$fecha || !$idProducto || $qq <= 0 || $precio <= 0) {
    header("Location: ../CONT_Ventas.php?error=" . urlencode("Datos incompletos."));
    exit();
}

$total = round($qq * $precio, 2);
$valorRetencionExacto = $total * $porcRetencion;
$totalACobrar = round($total - $valorRetencionExacto, 2);
$valorRetencion = round($valorRetencionExacto, 2);

if ($cliente !== '') {
    $stmtChk = mysqli_prepare($conexion, "SELECT ID_CLIENTE FROM CONT_CLIENTE WHERE NOMBRE = ?");
    mysqli_stmt_bind_param($stmtChk, "s", $cliente);
    mysqli_stmt_execute($stmtChk);
    if (!mysqli_stmt_get_result($stmtChk)->fetch_assoc()) {
        $stmtNew = mysqli_prepare($conexion, "INSERT INTO CONT_CLIENTE (NOMBRE) VALUES (?)");
        mysqli_stmt_bind_param($stmtNew, "s", $cliente);
        mysqli_stmt_execute($stmtNew);
    }
}

$stmt = mysqli_prepare($conexion, "INSERT INTO CONT_VENTA (FECHA, ID_PRODUCTO, CLIENTE, FACTURA_NO, TIPO, QQ, PRECIO, TOTAL, PORC_RETENCION, VALOR_RETENCION, TOTAL_A_COBRAR, OBSERVACION, ESTADO) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'A')");
mysqli_stmt_bind_param($stmt, "sisssdddddds", $fecha, $idProducto, $cliente, $facturaNo, $tipo, $qq, $precio, $total, $porcRetencion, $valorRetencion, $totalACobrar, $observacion);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../CONT_Ventas.php?success=1");
} else {
    header("Location: ../CONT_Ventas.php?error=" . urlencode(mysqli_error($conexion)));
}
mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
