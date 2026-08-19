<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$idCamara         = (int)($_POST['idCamara'] ?? 0);
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
$estado           = ($_POST['estado'] ?? 'A') === 'I' ? 'I' : 'A';

if (!$idCamara || !$marca) {
    echo "<script>alert('Datos incompletos.'); history.back();</script>";
    exit();
}

$fotoSql = '';
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    if (in_array($extension, $extensionesPermitidas)) {
        if (!is_dir(__DIR__ . '/../images/cctv')) {
            mkdir(__DIR__ . '/../images/cctv', 0755, true);
        }
        $nombreArchivo = 'camara_' . $idCamara . '_' . time() . '.' . $extension;
        $rutaDestino = __DIR__ . '/../images/cctv/' . $nombreArchivo;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
            $fotoSql = ", FOTO = '" . mysqli_real_escape_string($conexion, 'images/cctv/' . $nombreArchivo) . "'";
        }
    }
}

$sql = "UPDATE CCTV_CAMARA SET
    TIPO_CAMARA='$tipoCamara', MARCA='$marca', MODELO='$modelo', ID_DVR=$idDvr, IP='$ip',
    NUMERO_SERIE='$numeroSerie', USUARIO='$usuario', CLAVE='$clave', CLAVE_HIKCONNECT='$claveHikconnect',
    UBICACION='$ubicacion', FECHA_COMPRA=$fechaCompraSql, OBSERVACION='$observacion', ESTADO='$estado' $fotoSql
    WHERE ID_CAMARA = $idCamara";

if ($conexion->query($sql) === TRUE) {
    echo "<script>alert('Cámara actualizada correctamente.'); window.location.href = '../CCTV_Camaras.php';</script>";
} else {
    echo "<script>alert('Error al actualizar: " . addslashes($conexion->error) . "'); history.back();</script>";
}
$conexion->close();
?>
