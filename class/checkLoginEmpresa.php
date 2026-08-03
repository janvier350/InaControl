<?php
require_once("conexionBD_MT.php");

$username = isset($_POST['user'])     ? trim($_POST['user'])     : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if (!$username || !$password) {
    header("Location: ../EMPRESA_login.php?error=" . urlencode("Usuario y clave son requeridos."));
    exit();
}

$conMT = conectarse_MT();
if (!$conMT) {
    header("Location: ../EMPRESA_login.php?error=" . urlencode("No se pudo conectar a la base de datos."));
    exit();
}

$u = mysqli_real_escape_string($conMT, $username);
$sql = "SELECT u.IDADM_USUARIO, u.ID_EMPRESA, u.NOMBRES, u.APELLIDOS, u.USUARIO, u.CONTRASENA, r.ROL,
               e.NOMBRE AS NOMBRE_EMPRESA, c.MOD_SOPORTE, c.MOD_INVENTARIO, c.MOD_CORREOS, c.MOD_VENTAS, c.MOD_REPORTES
        FROM ADM_USUARIO u
        INNER JOIN ADM_ROL r ON u.IDADM_ROL = r.IDADM_ROL
        INNER JOIN EMPRESA e ON u.ID_EMPRESA = e.ID_EMPRESA
        LEFT JOIN EMPRESA_CONFIG c ON e.ID_EMPRESA = c.ID_EMPRESA
        WHERE u.USUARIO = '$u' AND u.ESTADO = 'A' AND r.ROL != 'SUPERADMIN' AND e.ESTADO = 'A'";

$res = mysqli_query($conMT, $sql);
if (!$res) {
    header("Location: ../EMPRESA_login.php?error=" . urlencode("Error en consulta: " . mysqli_error($conMT)));
    exit();
}

$row = mysqli_fetch_assoc($res);
mysqli_close($conMT);

if (!$row) {
    header("Location: ../EMPRESA_login.php?error=" . urlencode("Usuario o clave incorrectos."));
    exit();
}

$stored = $row['CONTRASENA'];
$ok = (strlen($stored) === 32) ? (md5($password) === $stored) : password_verify($password, $stored);

if (!$ok) {
    header("Location: ../EMPRESA_login.php?error=" . urlencode("Usuario o clave incorrectos."));
    exit();
}

session_start();
$_SESSION['loggedin']      = true;
$_SESSION['username']      = $row['USUARIO'];
$_SESSION['iduser']        = $row['IDADM_USUARIO'];
$_SESSION['id_empresa']    = $row['ID_EMPRESA'];
$_SESSION['nombre_empresa']= $row['NOMBRE_EMPRESA'];
$_SESSION['rol']           = $row['ROL'];
$_SESSION['mod_soporte']    = (int)$row['MOD_SOPORTE'];
$_SESSION['mod_inventario'] = (int)$row['MOD_INVENTARIO'];
$_SESSION['mod_correos']    = (int)$row['MOD_CORREOS'];
$_SESSION['mod_ventas']     = (int)$row['MOD_VENTAS'];
$_SESSION['mod_reportes']   = (int)$row['MOD_REPORTES'];
$_SESSION['start']         = time();
$_SESSION['expire']        = $_SESSION['start'] + (60 * 60);

header("Location: ../MT_Calendar_SOP.php");
exit();
