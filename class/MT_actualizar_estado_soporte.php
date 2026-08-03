<?php
ob_start();
session_start();
require_once("conexionBD_MT.php");

if (!isset($_SESSION['id_empresa'])) {
    header("Location: ../EMPRESA_login.php?error=" . urlencode("Sesión inválida."));
    exit();
}

$con = conectarse_MT();
if (!$con) {
    header("Location: ../MT_Calendar_SOP.php?error=" . urlencode("No se pudo conectar a la base de datos."));
    exit();
}

$idEmpresa = (int)$_SESSION['id_empresa'];
$id     = isset($_POST['id'])     ? (int)$_POST['id']     : null;
$estado = isset($_POST['estado']) ? trim($_POST['estado']) : null;

if ($id === null || $estado === null) {
    header("Location: ../MT_Calendar_SOP.php?error=datos_incompletos");
    exit();
}

$verificar = mysqli_prepare($con, "SELECT ID_CALENDARIO_SOPORTE FROM COTI_CALENDARIO WHERE ID_CALENDARIO_SOPORTE = ? AND ID_EMPRESA = ?");
mysqli_stmt_bind_param($verificar, "ii", $id, $idEmpresa);
mysqli_stmt_execute($verificar);
mysqli_stmt_store_result($verificar);

if (mysqli_stmt_num_rows($verificar) === 0) {
    mysqli_stmt_close($verificar);
    header("Location: ../MT_Calendar_SOP.php?error=cita_no_encontrada");
    exit();
}
mysqli_stmt_close($verificar);

$stmt = mysqli_prepare($con, "UPDATE COTI_CALENDARIO SET ESTADO_SOPORTE = ? WHERE ID_CALENDARIO_SOPORTE = ? AND ID_EMPRESA = ?");
mysqli_stmt_bind_param($stmt, "sii", $estado, $id, $idEmpresa);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../MT_Calendar_SOP.php?success=1");
} else {
    header("Location: ../MT_Calendar_SOP.php?error=" . urlencode(mysqli_error($con)));
}

mysqli_stmt_close($stmt);
mysqli_close($con);
exit();
