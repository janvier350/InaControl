<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION["rol"])) {
    http_response_code(403);
    echo json_encode(["error" => "No autorizado"]);
    exit();
}

$subred = isset($_GET['subred']) ? trim($_GET['subred']) : '';
$desde  = isset($_GET['desde']) ? (int)$_GET['desde'] : 1;
$hasta  = isset($_GET['hasta']) ? (int)$_GET['hasta'] : 254;

if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}$/', $subred)) {
    echo json_encode(["error" => "Subred inválida. Use el formato 192.168.11"]);
    exit();
}
$desde = max(1, min(254, $desde));
$hasta = max($desde, min(254, $hasta));

$usadas = []; // octeto => [tipo, etiqueta]

$likeSubred = $subred . '.%';

$qc = $conexion->prepare("SELECT IP, MARCA, MODELO, UBICACION FROM CCTV_CAMARA WHERE IP LIKE ? AND ESTADO = 'A'");
$qc->bind_param("s", $likeSubred);
$qc->execute();
$resC = $qc->get_result();
while ($row = $resC->fetch_assoc()) {
    $partes = explode('.', $row['IP']);
    $octeto = (int) end($partes);
    $usadas[$octeto] = ['tipo' => 'Cámara', 'etiqueta' => trim($row['MARCA'].' '.$row['MODELO']).' ('.$row['UBICACION'].')'];
}
$qc->close();

$qd = $conexion->prepare("SELECT IP, TIPO, MARCA, MODELO, UBICACION FROM CCTV_DVR WHERE IP LIKE ? AND ESTADO = 'A'");
$qd->bind_param("s", $likeSubred);
$qd->execute();
$resD = $qd->get_result();
while ($row = $resD->fetch_assoc()) {
    $partes = explode('.', $row['IP']);
    $octeto = (int) end($partes);
    $usadas[$octeto] = ['tipo' => $row['TIPO'], 'etiqueta' => trim($row['MARCA'].' '.$row['MODELO']).' ('.$row['UBICACION'].')'];
}
$qd->close();

$disponibles = [];
$ocupadas = [];
for ($i = $desde; $i <= $hasta; $i++) {
    if (isset($usadas[$i])) {
        $ocupadas[] = [
            "ip" => "$subred.$i",
            "tipo" => $usadas[$i]['tipo'],
            "etiqueta" => $usadas[$i]['etiqueta'],
        ];
    } else {
        $disponibles[] = "$subred.$i";
    }
}

echo json_encode([
    "success" => true,
    "subred" => $subred,
    "desde" => $desde,
    "hasta" => $hasta,
    "disponibles" => $disponibles,
    "ocupadas" => $ocupadas,
]);

$conexion->close();
exit();
