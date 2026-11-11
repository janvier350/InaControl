<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$desde = (int)($_POST['desde'] ?? 0);
$hasta = (int)($_POST['hasta'] ?? 0);
$observacion = trim($_POST['observacion'] ?? '');

if (!$desde || !$hasta || $hasta < $desde) {
    header("Location: ../CONT_Bascula.php?error=" . urlencode("Rango inválido."));
    exit();
}

if (($hasta - $desde) > 5000) {
    header("Location: ../CONT_Bascula.php?error=" . urlencode("El rango es demasiado grande (máximo 5000 números)."));
    exit();
}

try {
    $conexion->begin_transaction();

    $stmtRango = mysqli_prepare($conexion, "INSERT INTO CONT_BASCULA_RANGO (DESDE, HASTA, OBSERVACION) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmtRango, "iis", $desde, $hasta, $observacion);
    mysqli_stmt_execute($stmtRango);
    $idRango = $conexion->insert_id;

    $stmtSec = mysqli_prepare($conexion, "INSERT INTO CONT_BASCULA_SECUENCIA (NUMERO, ID_RANGO, ESTADO) VALUES (?, ?, 'PENDIENTE')");
    for ($n = $desde; $n <= $hasta; $n++) {
        mysqli_stmt_bind_param($stmtSec, "ii", $n, $idRango);
        mysqli_stmt_execute($stmtSec);
    }

    $conexion->commit();
    header("Location: ../CONT_Bascula.php?success=1");
} catch (mysqli_sql_exception $e) {
    $conexion->rollback();
    header("Location: ../CONT_Bascula.php?error=" . urlencode("No se pudo cargar el rango (posible número duplicado)."));
}
$conexion->close();
?>
