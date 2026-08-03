<?php
require_once("conexionBD.php");
require_once("conexionBD_MT.php");

$username = isset($_POST['user'])     ? trim($_POST['user'])     : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if (!$username || !$password) {
    echo "Usuario o Password estan vacios.";
    echo "<br><a href='../index.php'>Volver a Intentarlo</a>";
    exit();
}

// --- Check SUPERADMIN in MT database (solo si la BD esta disponible) ---
$conMT = conectarse_MT();
if ($conMT) {
    $u = mysqli_real_escape_string($conMT, $username);
    $sql = "SELECT u.IDADM_USUARIO, u.USUARIO, u.CONTRASENA, r.CARGO
            FROM ADM_USUARIO u
            INNER JOIN ADM_ROL r ON u.IDADM_ROL = r.IDADM_ROL
            WHERE u.USUARIO = '$u' AND u.ESTADO = 'A' AND r.CARGO = 'SUPERADMIN'";
    $res = mysqli_query($conMT, $sql);
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        if ($row) {
            $stored = $row['CONTRASENA'];
            $ok = (strlen($stored) === 32) ? (md5($password) === $stored) : password_verify($password, $stored);
            if ($ok) {
                session_start();
                $_SESSION['loggedin']  = true;
                $_SESSION['username']  = $row['USUARIO'];
                $_SESSION['iduser']    = $row['IDADM_USUARIO'];
                $_SESSION['rol']       = $row['CARGO'];
                $_SESSION['start']     = time();
                $_SESSION['expire']    = $_SESSION['start'] + (60 * 60 * 4);
                mysqli_close($conMT);
                echo "<script>self.location='../ADMIN_Empresas.php';</script>";
                exit();
            }
        }
    }
    mysqli_close($conMT);
}

// --- Login regular (BD existente) ---
$conexion = conectarse();

$u2  = mysqli_real_escape_string($conexion, $username);
$sql = "SELECT a.IDADM_USUARIO, a.USUARIO, a.CONTRASENA, b.CARGO
        FROM ADM_USUARIO a
        INNER JOIN ADM_ROL b ON a.IDADM_ROL = b.IDADM_ROL
        WHERE a.USUARIO = '$u2' AND a.ESTADO = 'A'";

$result = mysqli_query($conexion, $sql);
$row    = mysqli_fetch_assoc($result);

if ($row) {
    $stored = $row['CONTRASENA'];
    $ok = password_verify($password, $stored) || (md5($password) === $stored);
    if ($ok) {
        session_start();
        $_SESSION['loggedin']  = true;
        $_SESSION['username']  = $row['USUARIO'];
        $_SESSION['iduser']    = $row['IDADM_USUARIO'];
        $_SESSION['rol']       = $row['CARGO'];
        $_SESSION['start']     = time();
        $_SESSION['expire']    = $_SESSION['start'] + (60 * 60);
        mysqli_close($conexion);
        echo "<script>self.location='../SCH_Calendar_SOP.php';</script>";
        exit();
    }
}

mysqli_close($conexion);
echo "Usuario o Password estan incorrectos.";
echo "<br><a href='../index.php'>Volver a Intentarlo</a>";
