<?php
ob_start();
session_start();
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();

$idCorreo = isset($_GET['idCorreo']) ? (int)$_GET['idCorreo'] : null;

if (!$idCorreo) {
    header("Location: ../COR_Correos.php?error=datos_incompletos");
    exit();
}

$stmt = mysqli_prepare($conexion, "UPDATE COR_CORREO SET ESTADO='I' WHERE ID_CORREO=?");
mysqli_stmt_bind_param($stmt, "i", $idCorreo);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../COR_Correos.php?success=correo_eliminado");
} else {
    header("Location: ../COR_Correos.php?error=" . urlencode(mysqli_error($conexion)));
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
exit();
