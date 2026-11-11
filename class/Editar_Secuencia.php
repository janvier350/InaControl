<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$idSecuencia = (int)($_POST['idSecuencia'] ?? 0);
$estado = $_POST['estado'] ?? 'PENDIENTE';
$estadosValidos = ['PENDIENTE', 'USADO', 'ANULADO'];
if (!in_array($estado, $estadosValidos, true)) { $estado = 'PENDIENTE'; }

$fecha = trim($_POST['fecha'] ?? '');
$fechaSql = $fecha !== '' ? "'" . mysqli_real_escape_string($conexion, $fecha) . "'" : 'NULL';

$proveedorCliente = mysqli_real_escape_string($conexion, trim($_POST['proveedorCliente'] ?? ''));
$pesoBruto = $_POST['pesoBruto'] !== '' ? (float)$_POST['pesoBruto'] : null;
$tara = $_POST['tara'] !== '' ? (float)$_POST['tara'] : null;
$pesoNeto = ($pesoBruto !== null && $tara !== null) ? ($pesoBruto - $tara) : null;
$observacion = mysqli_real_escape_string($conexion, trim($_POST['observacion'] ?? ''));

$pesoBrutoSql = $pesoBruto !== null ? $pesoBruto : 'NULL';
$taraSql = $tara !== null ? $tara : 'NULL';
$pesoNetoSql = $pesoNeto !== null ? $pesoNeto : 'NULL';

if (!$idSecuencia) {
    header("Location: ../CONT_Bascula.php?error=" . urlencode("Secuencia inválida."));
    exit();
}

$sql = "UPDATE CONT_BASCULA_SECUENCIA SET
            ESTADO = '$estado',
            FECHA = $fechaSql,
            PROVEEDOR_CLIENTE = '$proveedorCliente',
            PESO_BRUTO = $pesoBrutoSql,
            TARA = $taraSql,
            PESO_NETO = $pesoNetoSql,
            OBSERVACION = '$observacion'
        WHERE ID_SECUENCIA = $idSecuencia";

if ($conexion->query($sql) === TRUE) {
    header("Location: ../CONT_Bascula.php?success=1");
} else {
    header("Location: ../CONT_Bascula.php?error=" . urlencode($conexion->error));
}
$conexion->close();
?>
