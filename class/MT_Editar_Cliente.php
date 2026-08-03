<?php
session_start();
require_once("conexionBD_MT.php");

if (!isset($_SESSION['id_empresa'])) {
    header("Location: ../EMPRESA_login.php?error=" . urlencode("Sesión inválida."));
    exit();
}

$con = conectarse_MT();
if (!$con) {
    header("Location: ../MT_Clientes.php?error=" . urlencode("No se pudo conectar a la base de datos."));
    exit();
}

$idEmpresa   = (int)$_SESSION['id_empresa'];
$idCliente   = (int)($_POST['idCliente'] ?? 0);
$nombres     = trim($_POST['nombres'] ?? '');
$apellidos   = trim($_POST['apellidos'] ?? '');
$razonSocial = trim($_POST['razonSocial'] ?? '');
$ruc         = trim($_POST['ruc'] ?? '');
$email       = trim($_POST['email'] ?? '');
$telefono    = trim($_POST['telefono'] ?? '');
$estado      = ($_POST['estado'] ?? 'A') === 'I' ? 'I' : 'A';

if (!$idCliente || (!$nombres && !$razonSocial)) {
    header("Location: ../MT_Clientes.php?error=" . urlencode("Datos incompletos."));
    exit();
}

$stmt = mysqli_prepare($con,
    "UPDATE COTI_CLIENTE SET NOMBRES=?, APELLIDOS=?, RAZON_SOCIAL=?, RUC=?, EMAIL=?, TELEFONO=?, ESTADO=?
     WHERE ID_CLIENTE=? AND ID_EMPRESA=?"
);
mysqli_stmt_bind_param($stmt, "sssssssii", $nombres, $apellidos, $razonSocial, $ruc, $email, $telefono, $estado, $idCliente, $idEmpresa);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../MT_Clientes.php?success=1");
} else {
    header("Location: ../MT_Clientes.php?error=" . urlencode(mysqli_error($con)));
}

mysqli_stmt_close($stmt);
mysqli_close($con);
exit();
