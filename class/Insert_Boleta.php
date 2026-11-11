<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$fecha = mysqli_real_escape_string($conexion, trim($_POST['fecha'] ?? ''));
$proveedor = mysqli_real_escape_string($conexion, trim($_POST['proveedor'] ?? ''));
$idSecuencia = $_POST['idSecuencia'] !== '' ? (int)$_POST['idSecuencia'] : null;
$pesoBruto = (float)($_POST['pesoBruto'] ?? 0);
$tara = (float)($_POST['tara'] ?? 0);
$porcCalificacion = (float)($_POST['porcCalificacion'] ?? 0);
$observacion = mysqli_real_escape_string($conexion, trim($_POST['observacion'] ?? ''));

if ($fecha === '' || $proveedor === '' || $pesoBruto <= 0 || $tara < 0 || $pesoBruto <= $tara) {
    header("Location: ../CONT_Compras.php?error=" . urlencode("Datos de boleta inválidos."));
    exit();
}

$pesoNeto = $pesoBruto - $tara;
$qq = $pesoNeto * 0.011;
$qqCalificado = $qq * $porcCalificacion;
$qqNeto = $qq - $qqCalificado;

$numeroBascula = null;
if ($idSecuencia) {
    $stmtSec = mysqli_prepare($conexion, "SELECT NUMERO FROM CONT_BASCULA_SECUENCIA WHERE ID_SECUENCIA = ?");
    mysqli_stmt_bind_param($stmtSec, "i", $idSecuencia);
    mysqli_stmt_execute($stmtSec);
    $resSec = mysqli_stmt_get_result($stmtSec);
    if ($rowSec = mysqli_fetch_assoc($resSec)) {
        $numeroBascula = $rowSec['NUMERO'];
    }
}

try {
    $conexion->begin_transaction();

    $stmt = mysqli_prepare($conexion, "INSERT INTO CONT_COMPRA_BOLETA
        (FECHA, PROVEEDOR, ID_SECUENCIA, NUMERO_BASCULA, PESO_BRUTO, TARA, PESO_NETO, QQ, PORC_CALIFICACION, QQ_CALIFICADO, QQ_NETO, OBSERVACION)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssiiddddddds",
        $fecha, $proveedor, $idSecuencia, $numeroBascula, $pesoBruto, $tara, $pesoNeto, $qq, $porcCalificacion, $qqCalificado, $qqNeto, $observacion);
    mysqli_stmt_execute($stmt);

    if ($idSecuencia) {
        $stmtUp = mysqli_prepare($conexion, "UPDATE CONT_BASCULA_SECUENCIA
            SET ESTADO = 'USADO', FECHA = ?, PROVEEDOR_CLIENTE = ?, PESO_BRUTO = ?, TARA = ?, PESO_NETO = ?
            WHERE ID_SECUENCIA = ?");
        mysqli_stmt_bind_param($stmtUp, "ssdddi", $fecha, $proveedor, $pesoBruto, $tara, $pesoNeto, $idSecuencia);
        mysqli_stmt_execute($stmtUp);
    }

    $conexion->commit();
    header("Location: ../CONT_Compras.php?success=1");
} catch (mysqli_sql_exception $e) {
    $conexion->rollback();
    header("Location: ../CONT_Compras.php?error=" . urlencode("No se pudo registrar la boleta."));
}
$conexion->close();
?>
