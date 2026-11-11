<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$fecha = mysqli_real_escape_string($conexion, trim($_POST['fecha'] ?? ''));
$proveedor = mysqli_real_escape_string($conexion, trim($_POST['proveedor'] ?? ''));
$facturaNo = mysqli_real_escape_string($conexion, trim($_POST['facturaNo'] ?? ''));
$precio = (float)($_POST['precio'] ?? 0);
$observacion = mysqli_real_escape_string($conexion, trim($_POST['observacion'] ?? ''));
$idsBoletas = $_POST['idsBoletas'] ?? [];

if ($fecha === '' || $proveedor === '' || $precio <= 0 || empty($idsBoletas)) {
    header("Location: ../CONT_Compras.php?error=" . urlencode("Seleccione al menos una boleta y complete los datos."));
    exit();
}

$idsBoletas = array_map('intval', $idsBoletas);
$placeholders = implode(',', array_fill(0, count($idsBoletas), '?'));
$types = str_repeat('i', count($idsBoletas));

try {
    $conexion->begin_transaction();

    $stmtSel = mysqli_prepare($conexion, "SELECT COALESCE(SUM(QQ_NETO),0) AS QQ_TOTAL FROM CONT_COMPRA_BOLETA
        WHERE ID_BOLETA IN ($placeholders) AND ID_LIQUIDACION IS NULL");
    mysqli_stmt_bind_param($stmtSel, $types, ...$idsBoletas);
    mysqli_stmt_execute($stmtSel);
    $resSel = mysqli_stmt_get_result($stmtSel);
    $qqTotal = (float)mysqli_fetch_assoc($resSel)['QQ_TOTAL'];

    if ($qqTotal <= 0) {
        throw new Exception("Las boletas seleccionadas ya no están disponibles.");
    }

    $total = $qqTotal * $precio;

    $stmtLiq = mysqli_prepare($conexion, "INSERT INTO CONT_COMPRA_LIQUIDACION
        (FECHA, PROVEEDOR, FACTURA_NO, QQ_TOTAL, PRECIO, TOTAL, OBSERVACION) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmtLiq, "sssddds", $fecha, $proveedor, $facturaNo, $qqTotal, $precio, $total, $observacion);
    mysqli_stmt_execute($stmtLiq);
    $idLiquidacion = $conexion->insert_id;

    $sqlUp = "UPDATE CONT_COMPRA_BOLETA SET ID_LIQUIDACION = ? WHERE ID_BOLETA IN ($placeholders) AND ID_LIQUIDACION IS NULL";
    $stmtUp = mysqli_prepare($conexion, $sqlUp);
    mysqli_stmt_bind_param($stmtUp, "i" . $types, $idLiquidacion, ...$idsBoletas);
    mysqli_stmt_execute($stmtUp);

    $conexion->commit();
    header("Location: ../CONT_Compras.php?success=1");
} catch (Exception $e) {
    $conexion->rollback();
    header("Location: ../CONT_Compras.php?error=" . urlencode($e->getMessage()));
}
$conexion->close();
?>
