<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

if (!isset($_SESSION["rol"])) {
    header("Location: ../index.php");
    exit();
}

$sql = "SELECT C.*, D.TIPO AS DVR_TIPO, D.MARCA AS DVR_MARCA, D.MODELO AS DVR_MODELO, D.UBICACION AS DVR_UBICACION
        FROM CCTV_CAMARA C
        LEFT JOIN CCTV_DVR D ON C.ID_DVR = D.ID_DVR
        WHERE C.ESTADO = 'A'
        ORDER BY C.UBICACION, C.MARCA";
$resultados = $conexion->query($sql);

$nombreArchivo = 'reporte_camaras_' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');

echo chr(0xEF) . chr(0xBB) . chr(0xBF); // BOM para Excel

$out = fopen('php://output', 'w');
fputcsv($out, [
    'Tipo', 'Marca', 'Modelo', 'IP', 'Número de Serie', 'Usuario',
    'DVR / NVR', 'Ubicación DVR', 'Compartida HikConnect', 'Ubicación / Área',
    'Fecha de Compra', 'Observación', 'Estado'
], ';');

while ($c = mysqli_fetch_assoc($resultados)) {
    $dvrLabel = $c['ID_DVR'] ? trim($c['DVR_TIPO'] . ' ' . $c['DVR_MARCA'] . ' ' . $c['DVR_MODELO']) : 'Independiente';
    fputcsv($out, [
        $c['TIPO_CAMARA'],
        $c['MARCA'],
        $c['MODELO'],
        $c['IP'],
        $c['NUMERO_SERIE'],
        $c['USUARIO'],
        $dvrLabel,
        $c['DVR_UBICACION'],
        !empty($c['COMPARTIDA_HIKCONNECT']) ? 'Sí' : 'No',
        $c['UBICACION'],
        $c['FECHA_COMPRA'] ? date('d/m/Y', strtotime($c['FECHA_COMPRA'])) : '',
        $c['OBSERVACION'],
        $c['ESTADO'] === 'A' ? 'Activa' : 'Inactiva',
    ], ';');
}

fclose($out);
$conexion->close();
exit();
