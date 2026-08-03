<?php
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

$idEmpresa   = (int)$_SESSION['id_empresa'];
$nombres     = trim($_POST['nombres'] ?? '');
$apellidos   = trim($_POST['apellidos'] ?? '');
$razonSocial = trim($_POST['razonSocial'] ?? '');
$email       = trim($_POST['email'] ?? '');
$telefono    = trim($_POST['telefono'] ?? '');

if (!$nombres && !$razonSocial) {
    header("Location: ../MT_Calendar_SOP.php?error=" . urlencode("Ingrese al menos nombres o razón social."));
    exit();
}

$stmt = mysqli_prepare($con,
    "INSERT INTO COTI_CLIENTE (ID_EMPRESA, NOMBRES, APELLIDOS, RAZON_SOCIAL, EMAIL, TELEFONO, ESTADO)
     VALUES (?, ?, ?, ?, ?, ?, 'A')"
);
mysqli_stmt_bind_param($stmt, "isssss", $idEmpresa, $nombres, $apellidos, $razonSocial, $email, $telefono);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
mysqli_close($con);

header("Location: ../MT_Calendar_SOP.php?success=cliente_creado");
exit();
