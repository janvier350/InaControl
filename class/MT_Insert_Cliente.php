<?php
session_start();
require_once("conexionBD_MT.php");

if (!isset($_SESSION['id_empresa'])) {
    header("Location: ../EMPRESA_login.php?error=" . urlencode("Sesión inválida."));
    exit();
}

$con = conectarse_MT();
$destino = ($_POST['redirect'] ?? '') === 'MT_Clientes.php' ? '../MT_Clientes.php' : '../MT_Calendar_SOP.php';

if (!$con) {
    header("Location: $destino?error=" . urlencode("No se pudo conectar a la base de datos."));
    exit();
}

$idEmpresa   = (int)$_SESSION['id_empresa'];
$nombres     = trim($_POST['nombres'] ?? '');
$apellidos   = trim($_POST['apellidos'] ?? '');
$razonSocial = trim($_POST['razonSocial'] ?? '');
$ruc         = trim($_POST['ruc'] ?? '');
$email       = trim($_POST['email'] ?? '');
$telefono    = trim($_POST['telefono'] ?? '');

if (!$nombres && !$razonSocial) {
    header("Location: $destino?error=" . urlencode("Ingrese al menos nombres o razón social."));
    exit();
}

$stmt = mysqli_prepare($con,
    "INSERT INTO COTI_CLIENTE (ID_EMPRESA, NOMBRES, APELLIDOS, RAZON_SOCIAL, RUC, EMAIL, TELEFONO, ESTADO)
     VALUES (?, ?, ?, ?, ?, ?, ?, 'A')"
);
mysqli_stmt_bind_param($stmt, "issssss", $idEmpresa, $nombres, $apellidos, $razonSocial, $ruc, $email, $telefono);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
mysqli_close($con);

header("Location: $destino?success=cliente_creado");
exit();
