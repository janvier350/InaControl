<?php
require_once("conexionBD_MT.php");

$username = isset($_POST['user'])     ? trim($_POST['user'])     : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if (!$username || !$password) {
    header("Location: ../SUPERADMIN_login.php?error=" . urlencode("Usuario y clave son requeridos."));
    exit();
}

$conMT = conectarse_MT();
if (!$conMT) {
    header("Location: ../SUPERADMIN_login.php?error=" . urlencode("No se pudo conectar a la base de datos multi-tenant. Revisa db_config_MT.php."));
    exit();
}

$u = mysqli_real_escape_string($conMT, $username);
$sql = "SELECT u.IDADM_USUARIO, u.USUARIO, u.CONTRASENA, r.CARGO
        FROM ADM_USUARIO u
        INNER JOIN ADM_ROL r ON u.IDADM_ROL = r.IDADM_ROL
        WHERE u.USUARIO = '$u' AND u.ESTADO = 'A' AND r.CARGO = 'SUPERADMIN'";

$res = mysqli_query($conMT, $sql);
if (!$res) {
    header("Location: ../SUPERADMIN_login.php?error=" . urlencode("Error en consulta: " . mysqli_error($conMT)));
    exit();
}

$row = mysqli_fetch_assoc($res);
mysqli_close($conMT);

if (!$row) {
    header("Location: ../SUPERADMIN_login.php?error=" . urlencode("Usuario SUPERADMIN no encontrado."));
    exit();
}

$stored = $row['CONTRASENA'];
$ok = (strlen($stored) === 32) ? (md5($password) === $stored) : password_verify($password, $stored);

if (!$ok) {
    header("Location: ../SUPERADMIN_login.php?error=" . urlencode("Clave incorrecta."));
    exit();
}

session_start();
$_SESSION['loggedin'] = true;
$_SESSION['username']  = $row['USUARIO'];
$_SESSION['iduser']    = $row['IDADM_USUARIO'];
$_SESSION['rol']       = $row['CARGO'];
$_SESSION['start']     = time();
$_SESSION['expire']    = $_SESSION['start'] + (60 * 60 * 4);

header("Location: ../ADMIN_Empresas.php");
exit();
