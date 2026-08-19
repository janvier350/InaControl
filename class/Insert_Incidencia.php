<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$equipo      = trim($_POST['equipo'] ?? '');
$tipo        = mysqli_real_escape_string($conexion, trim($_POST['tipo'] ?? 'Falla'));
$fecha       = mysqli_real_escape_string($conexion, trim($_POST['fecha'] ?? ''));
$descripcion = mysqli_real_escape_string($conexion, trim($_POST['descripcion'] ?? ''));

if (!$equipo || !$fecha || !$descripcion) {
    echo "<script>alert('Datos incompletos.'); history.back();</script>";
    exit();
}

$idCamara = 'NULL';
$idDvr = 'NULL';

if (strpos($equipo, 'camara-') === 0) {
    $idCamara = (int) substr($equipo, 7);
} elseif (strpos($equipo, 'dvr-') === 0) {
    $idDvr = (int) substr($equipo, 4);
} else {
    echo "<script>alert('Equipo inválido.'); history.back();</script>";
    exit();
}

// Procesar evidencias fotográficas (opcional)
$evidencias = [];
if (!empty($_FILES['evidencias']) && is_array($_FILES['evidencias']['name'])) {
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $totalArchivos = count($_FILES['evidencias']['name']);

    if (!is_dir(__DIR__ . '/../images/cctv')) {
        mkdir(__DIR__ . '/../images/cctv', 0755, true);
    }

    for ($i = 0; $i < $totalArchivos; $i++) {
        if ($_FILES['evidencias']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        $extension = strtolower(pathinfo($_FILES['evidencias']['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensionesPermitidas)) {
            continue;
        }
        $nombreArchivo = 'incidencia_' . time() . '_' . mt_rand(1000, 9999) . '_' . $i . '.' . $extension;
        $rutaDestino = __DIR__ . '/../images/cctv/' . $nombreArchivo;
        if (move_uploaded_file($_FILES['evidencias']['tmp_name'][$i], $rutaDestino)) {
            $evidencias[] = 'images/cctv/' . $nombreArchivo;
        }
    }
}
$evidenciasGuardar = mysqli_real_escape_string($conexion, implode(',', $evidencias));

$sql = "INSERT INTO CCTV_INCIDENCIA (ID_CAMARA, ID_DVR, TIPO, FECHA, DESCRIPCION, EVIDENCIAS, ESTADO)
        VALUES ($idCamara, $idDvr, '$tipo', '$fecha', '$descripcion', '$evidenciasGuardar', 'Abierta')";

if ($conexion->query($sql) === TRUE) {
    echo "<script>alert('Incidencia registrada correctamente.'); window.location.href = '../CCTV_Incidencias.php';</script>";
} else {
    echo "<script>alert('Error al registrar: " . addslashes($conexion->error) . "'); history.back();</script>";
}
$conexion->close();
?>
