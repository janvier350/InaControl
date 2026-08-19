<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$tipo             = mysqli_real_escape_string($conexion, trim($_POST['tipo'] ?? 'DVR'));
$marca            = mysqli_real_escape_string($conexion, trim($_POST['marca'] ?? ''));
$modelo           = mysqli_real_escape_string($conexion, trim($_POST['modelo'] ?? ''));
$canales          = isset($_POST['canales']) && $_POST['canales'] !== '' ? (int)$_POST['canales'] : 'NULL';
$ip               = mysqli_real_escape_string($conexion, trim($_POST['ip'] ?? ''));
$numeroSerie      = mysqli_real_escape_string($conexion, trim($_POST['numeroSerie'] ?? ''));
$claveAcceso      = mysqli_real_escape_string($conexion, trim($_POST['claveAcceso'] ?? ''));
$claveHikconnect  = mysqli_real_escape_string($conexion, trim($_POST['claveHikconnect'] ?? ''));
$ubicacion        = mysqli_real_escape_string($conexion, trim($_POST['ubicacion'] ?? ''));
$fechaCompra      = trim($_POST['fechaCompra'] ?? '');
$fechaCompraSql   = $fechaCompra !== '' ? "'" . mysqli_real_escape_string($conexion, $fechaCompra) . "'" : 'NULL';
$observacion      = mysqli_real_escape_string($conexion, trim($_POST['observacion'] ?? ''));
$comentario       = mysqli_real_escape_string($conexion, trim($_POST['comentario'] ?? ''));

if (!$marca) {
    echo "<script>alert('La marca es obligatoria.'); history.back();</script>";
    exit();
}

// Procesar foto (opcional)
$foto = '';
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    if (in_array($extension, $extensionesPermitidas)) {
        if (!is_dir(__DIR__ . '/../images/cctv')) {
            mkdir(__DIR__ . '/../images/cctv', 0755, true);
        }
        $nombreArchivo = 'dvr_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
        $rutaDestino = __DIR__ . '/../images/cctv/' . $nombreArchivo;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
            $foto = 'images/cctv/' . $nombreArchivo;
        }
    }
}

$sql = "INSERT INTO CCTV_DVR
    (TIPO, MARCA, MODELO, IP, CANALES, NUMERO_SERIE, CLAVE_ACCESO, CLAVE_HIKCONNECT, UBICACION, FECHA_COMPRA, OBSERVACION, COMENTARIO, FOTO, ESTADO)
    VALUES ('$tipo', '$marca', '$modelo', '$ip', $canales, '$numeroSerie', '$claveAcceso', '$claveHikconnect', '$ubicacion', $fechaCompraSql, '$observacion', '$comentario', '$foto', 'A')";

if ($conexion->query($sql) === TRUE) {
    echo "<script>alert('DVR/NVR registrado correctamente.'); window.location.href = '../CCTV_DVR.php';</script>";
} else {
    echo "<script>alert('Error al registrar: " . addslashes($conexion->error) . "'); history.back();</script>";
}
$conexion->close();
?>
