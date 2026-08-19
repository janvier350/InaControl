<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$tipoCamara       = mysqli_real_escape_string($conexion, trim($_POST['tipoCamara'] ?? 'IP'));
$marca            = mysqli_real_escape_string($conexion, trim($_POST['marca'] ?? ''));
$modelo           = mysqli_real_escape_string($conexion, trim($_POST['modelo'] ?? ''));
$idDvr            = isset($_POST['idDvr']) && $_POST['idDvr'] !== '' ? (int)$_POST['idDvr'] : 'NULL';
$ip               = mysqli_real_escape_string($conexion, trim($_POST['ip'] ?? ''));
$numeroSerie      = mysqli_real_escape_string($conexion, trim($_POST['numeroSerie'] ?? ''));
$usuario          = mysqli_real_escape_string($conexion, trim($_POST['usuario'] ?? ''));
$clave            = mysqli_real_escape_string($conexion, trim($_POST['clave'] ?? ''));
$claveHikconnect  = mysqli_real_escape_string($conexion, trim($_POST['claveHikconnect'] ?? ''));
$ubicacion        = mysqli_real_escape_string($conexion, trim($_POST['ubicacion'] ?? ''));
$fechaCompra      = trim($_POST['fechaCompra'] ?? '');
$fechaCompraSql   = $fechaCompra !== '' ? "'" . mysqli_real_escape_string($conexion, $fechaCompra) . "'" : 'NULL';
$observacion      = mysqli_real_escape_string($conexion, trim($_POST['observacion'] ?? ''));

if (!$marca) {
    echo "<script>alert('La marca es obligatoria.'); history.back();</script>";
    exit();
}

$foto = '';
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    if (in_array($extension, $extensionesPermitidas)) {
        if (!is_dir(__DIR__ . '/../images/cctv')) {
            mkdir(__DIR__ . '/../images/cctv', 0755, true);
        }
        $nombreArchivo = 'camara_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
        $rutaDestino = __DIR__ . '/../images/cctv/' . $nombreArchivo;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
            $foto = 'images/cctv/' . $nombreArchivo;
        }
    }
}

$sql = "INSERT INTO CCTV_CAMARA
    (ID_DVR, MARCA, MODELO, IP, NUMERO_SERIE, USUARIO, CLAVE, CLAVE_HIKCONNECT, TIPO_CAMARA, UBICACION, FECHA_COMPRA, OBSERVACION, FOTO, ESTADO)
    VALUES ($idDvr, '$marca', '$modelo', '$ip', '$numeroSerie', '$usuario', '$clave', '$claveHikconnect', '$tipoCamara', '$ubicacion', $fechaCompraSql, '$observacion', '$foto', 'A')";

if ($conexion->query($sql) === TRUE) {
    echo "<script>alert('Cámara registrada correctamente.'); window.location.href = '../CCTV_Camaras.php';</script>";
} else {
    echo "<script>alert('Error al registrar: " . addslashes($conexion->error) . "'); history.back();</script>";
}
$conexion->close();
?>
