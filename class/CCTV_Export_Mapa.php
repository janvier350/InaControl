<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

if (!isset($_SESSION["rol"])) {
    header("Location: ../index.php");
    exit();
}

$elaboradoPor = $_SESSION["username"];
if (!empty($_SESSION['iduser'])) {
    $stmtU = $conexion->prepare("SELECT NOMBRES, APELLIDOS FROM ADM_USUARIO WHERE IDADM_USUARIO = ?");
    $stmtU->bind_param("i", $_SESSION['iduser']);
    $stmtU->execute();
    $rowU = $stmtU->get_result()->fetch_assoc();
    $stmtU->close();
    if ($rowU) {
        $elaboradoPor = trim($rowU['NOMBRES'] . ' ' . $rowU['APELLIDOS']);
    }
}

$dvrs = [];
$q = $conexion->query("SELECT * FROM CCTV_DVR WHERE ESTADO = 'A' ORDER BY UBICACION, MARCA");
while ($d = mysqli_fetch_assoc($q)) {
    $d['CAMARAS'] = [];
    $dvrs[$d['ID_DVR']] = $d;
}

$independientes = [];
$qc = $conexion->query("SELECT * FROM CCTV_CAMARA WHERE ESTADO = 'A' ORDER BY UBICACION, MARCA");
while ($c = mysqli_fetch_assoc($qc)) {
    if ($c['ID_DVR'] && isset($dvrs[$c['ID_DVR']])) {
        $dvrs[$c['ID_DVR']]['CAMARAS'][] = $c;
    } else {
        $independientes[] = $c;
    }
}

$nombreArchivo = 'reporte_mapa_conexiones_cctv_' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');

echo chr(0xEF) . chr(0xBB) . chr(0xBF); // BOM para Excel

$out = fopen('php://output', 'w');

fputcsv($out, ['INASAR'], ';');
fputcsv($out, ['Mapa de Conexiones CCTV (DVR/NVR y cámaras conectadas)'], ';');
fputcsv($out, ['Elaborado por:', $elaboradoPor], ';');
fputcsv($out, ['Fecha de emisión:', date('d/m/Y H:i')], ';');
fputcsv($out, ['Departamento:', 'Sistemas / Soporte Técnico'], ';');
fputcsv($out, [], ';');

fputcsv($out, [
    'DVR/NVR', 'IP DVR/NVR', 'Ubicación DVR/NVR', 'Canales',
    'Cámara', 'Tipo Cámara', 'Ubicación Cámara', 'Compartida HikConnect',
], ';');

foreach ($dvrs as $d) {
    $dvrLabel = trim($d['TIPO'] . ' ' . $d['MARCA'] . ' ' . $d['MODELO']);
    if (empty($d['CAMARAS'])) {
        fputcsv($out, [$dvrLabel, $d['IP'], $d['UBICACION'], $d['CANALES'], 'Sin cámaras conectadas', '', '', ''], ';');
        continue;
    }
    foreach ($d['CAMARAS'] as $cam) {
        fputcsv($out, [
            $dvrLabel, $d['IP'], $d['UBICACION'], $d['CANALES'],
            trim($cam['MARCA'] . ' ' . $cam['MODELO']), $cam['TIPO_CAMARA'], $cam['UBICACION'],
            !empty($cam['COMPARTIDA_HIKCONNECT']) ? 'Sí' : 'No',
        ], ';');
    }
}

foreach ($independientes as $cam) {
    fputcsv($out, [
        'Independiente', '', '', '',
        trim($cam['MARCA'] . ' ' . $cam['MODELO']), $cam['TIPO_CAMARA'], $cam['UBICACION'],
        !empty($cam['COMPARTIDA_HIKCONNECT']) ? 'Sí' : 'No',
    ], ';');
}

fclose($out);
$conexion->close();
exit();
