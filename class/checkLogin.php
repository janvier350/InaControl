<?php
require_once("funciones.php");
require_once("conexionBD.php");
require_once("conexionBD_MT.php");

$username = isset($_POST['user'])     ? trim($_POST['user'])     : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if (!$username || !$password) {
    echo "Usuario o Password están vacíos.";
    echo "<br><a href='../index.php'>Volver a Intentarlo</a>";
    exit();
}

// --- Check SUPERADMIN in MT database first ---
$conMT = conectarse_MT();
$stmtSA = mysqli_prepare($conMT,
    "SELECT u.IDADM_USUARIO, u.NOMBRES, u.APELLIDOS, u.USUARIO, u.CONTRASENA, r.CARGO
     FROM ADM_USUARIO u
     INNER JOIN ADM_ROL r ON u.IDADM_ROL = r.IDADM_ROL
     WHERE u.USUARIO = ? AND u.ESTADO = 'A' AND r.CARGO = 'SUPERADMIN'"
);
mysqli_stmt_bind_param($stmtSA, "s", $username);
mysqli_stmt_execute($stmtSA);
$resSA = mysqli_stmt_get_result($stmtSA);
$rowSA = mysqli_fetch_assoc($resSA);
mysqli_stmt_close($stmtSA);
mysqli_close($conMT);

if ($rowSA) {
    $storedPass = $rowSA['CONTRASENA'];
    $match = false;
    if (strlen($storedPass) === 32) {
        // MD5
        $match = (md5($password) === $storedPass);
    } else {
        // bcrypt
        $match = password_verify($password, $storedPass);
    }

    if ($match) {
        session_start();
        $_SESSION['loggedin']  = true;
        $_SESSION['username']  = $username;
        $_SESSION['iduser']    = $rowSA['IDADM_USUARIO'];
        $_SESSION['rol']       = $rowSA['CARGO'];
        $_SESSION['start']     = time();
        $_SESSION['expire']    = $_SESSION['start'] + (60 * 60 * 4);
        echo "<script>self.location='../ADMIN_Empresas.php';</script>";
        exit();
    }
}

// --- Regular user login (existing single-tenant DB) ---
$conexion = conectarse();
if ($conexion->connect_error) {
    die("La conexion fallo: " . $conexion->connect_error);
}

$sql = "SELECT a.IDADM_USUARIO, a.NOMBRES, a.APELLIDOS, a.USUARIO, a.CONTRASENA, b.IDADM_ROL, b.CARGO
        FROM ADM_USUARIO a
        INNER JOIN ADM_ROL b ON a.IDADM_ROL = b.IDADM_ROL
        WHERE a.USUARIO = ? AND a.ESTADO = 'A'";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row    = mysqli_fetch_assoc($result);
$stmt->close();

if ($row && password_verify($password, $row['CONTRASENA'])) {
    session_start();
    $_SESSION['loggedin']  = true;
    $_SESSION['username']  = $username;
    $_SESSION['iduser']    = $row['IDADM_USUARIO'];
    $_SESSION['rol']       = $row['CARGO'];
    $_SESSION['start']     = time();
    $_SESSION['expire']    = $_SESSION['start'] + (60 * 60);
    mysqli_close($conexion);
    echo "<script>self.location='../SCH_Calendar_SOP.php';</script>";
    exit();
}

// Also try md5 for legacy users
if ($row && md5($password) === $row['CONTRASENA']) {
    session_start();
    $_SESSION['loggedin']  = true;
    $_SESSION['username']  = $username;
    $_SESSION['iduser']    = $row['IDADM_USUARIO'];
    $_SESSION['rol']       = $row['CARGO'];
    $_SESSION['start']     = time();
    $_SESSION['expire']    = $_SESSION['start'] + (60 * 60);
    mysqli_close($conexion);
    echo "<script>self.location='../SCH_Calendar_SOP.php';</script>";
    exit();
}

mysqli_close($conexion);
echo "Usuario o Password estan incorrectos.";
echo "<br><a href='../index.php'>Volver a Intentarlo</a>";
