<?php
session_start();
require_once("conexionBD_MT.php");

if (!isset($_SESSION['id_empresa'])) {
    header("Location: ../EMPRESA_login.php?error=" . urlencode("Sesión inválida."));
    exit();
}

$con = conectarse_MT();
if (!$con) {
    header("Location: ../MT_TiposSoporte.php?error=" . urlencode("No se pudo conectar a la base de datos."));
    exit();
}

$idEmpresa   = (int)$_SESSION['id_empresa'];
$idTipo      = (int)($_POST['idTipo'] ?? 0);
$soporte     = trim($_POST['soporte'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$estado      = ($_POST['estado'] ?? 'A') === 'I' ? 'I' : 'A';

if (!$idTipo || !$soporte) {
    header("Location: ../MT_TiposSoporte.php?error=" . urlencode("Datos incompletos."));
    exit();
}

$stmt = mysqli_prepare($con,
    "UPDATE COTI_TIPO_SOPORTE SET SOPORTE=?, DESCRIPCION=?, ESTADO=? WHERE ID_TIPO_SOPORTE=? AND ID_EMPRESA=?"
);
mysqli_stmt_bind_param($stmt, "sssii", $soporte, $descripcion, $estado, $idTipo, $idEmpresa);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
mysqli_close($con);

header("Location: ../MT_TiposSoporte.php?success=1");
exit();
