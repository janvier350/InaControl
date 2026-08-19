<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$idDvr            = (int)($_POST['idDvr'] ?? 0);
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
$estado           = ($_POST['estado'] ?? 'A') === 'I' ? 'I' : 'A';

if (!$idDvr || !$marca) {
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
        $nombreArchivo = 'dvr_' . $idDvr . '_' . time() . '.' . $extension;
        $rutaDestino = __DIR__ . '/../images/cctv/' . $nombreArchivo;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
            $fotoSql = ", FOTO = '" . mysqli_real_escape_string($conexion, 'images/cctv/' . $nombreArchivo) . "'";
        }
    }
}

$sql = "UPDATE CCTV_DVR SET
    TIPO='$tipo', MARCA='$marca', MODELO='$modelo', IP='$ip', CANALES=$canales,
    NUMERO_SERIE='$numeroSerie', CLAVE_ACCESO='$claveAcceso', CLAVE_HIKCONNECT='$claveHikconnect',
    UBICACION='$ubicacion', FECHA_COMPRA=$fechaCompraSql, OBSERVACION='$observacion',
    COMENTARIO='$comentario', ESTADO='$estado' $fotoSql
    WHERE ID_DVR = $idDvr";

if ($conexion->query($sql) === TRUE) {
    echo "<script>alert('DVR/NVR actualizado correctamente.'); window.location.href = '../CCTV_DVR.php';</script>";
} else {
    echo "<script>alert('Error al actualizar: " . addslashes($conexion->error) . "'); history.back();</script>";
}
$conexion->close();
?>
