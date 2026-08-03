<?php
require_once("funciones.php");
require_once("conexionBD.php");
require_once("conexionBD_MT.php");

$username = isset($_POST['user'])     ? trim($_POST['user'])     : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if (!$username || !$password) {
    echo "Usuario o Password estan vacios.";
    echo "<br><a href='../index.php'>Volver a Intentarlo</a>";
    exit();
}

// --- Check SUPERADMIN in MT database (only if MT DB is available) ---
$conMT = conectarse_MT();
if ($conMT) {
    $stmtSA = mysqli_prepare($conMT,
        "SELECT u.IDADM_USUARIO, u.NOMBRES, u.APELLIDOS, u.USUARIO, u.CONTRASENA, r.CARGO
         FROM ADM_USUARIO u
         INNER JOIN ADM_ROL r ON u.IDADM_ROL = r.IDADM_ROL
         WHERE u.USUARIO = ? AND u.ESTADO = 'A' AND r.CARGO = 'SUPERADMIN'"
    );
    if ($stmtSA) {
        mysqli_stmt_bind_param($stmtSA, "s", $username);
        mysqli_stmt_execute($stmtSA);
        mysqli_stmt_store_result($stmtSA);
        if (mysqli_stmt_num_rows($stmtSA) > 0) {
            mysqli_stmt_bind_result($stmtSA, $saId, $saNombres, $saApellidos, $saUsuario, $saContrasena, $saCargo);
            mysqli_stmt_fetch($stmtSA);
            mysqli_stmt_close($stmtSA);
            mysqli_close($conMT);

            $match = false;
            if (strlen($saContrasena) === 32) {
                $match = (md5($password) === $saContrasena);
            } else {
                $match = password_verify($password, $saContrasena);
            }
            if ($match) {
                session_start();
                $_SESSION['loggedin']  = true;
                $_SESSION['username']  = $saUsuario;
                $_SESSION['iduser']    = $saId;
                $_SESSION['rol']       = $saCargo;
                $_SESSION['start']     = time();
                $_SESSION['expire']    = $_SESSION['start'] + (60 * 60 * 4);
                echo "<script>self.location='../ADMIN_Empresas.php';</script>";
                exit();
            }
        } else {
            mysqli_stmt_close($stmtSA);
        }
    }
    mysqli_close($conMT);
}

// --- Regular user login (existing single-tenant DB) ---
$conexion = conectarse();

$stmt = mysqli_prepare($conexion,
    "SELECT a.IDADM_USUARIO, a.NOMBRES, a.APELLIDOS, a.USUARIO, a.CONTRASENA, b.CARGO
     FROM ADM_USUARIO a
     INNER JOIN ADM_ROL b ON a.IDADM_ROL = b.IDADM_ROL
     WHERE a.USUARIO = ? AND a.ESTADO = 'A'"
);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_bind_result($stmt, $uId, $uNombres, $uApellidos, $uUsuario, $uContrasena, $uCargo);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    $match = false;
    if (password_verify($password, $uContrasena)) {
        $match = true;
    } elseif (md5($password) === $uContrasena) {
        $match = true;
    }

    if ($match) {
        session_start();
        $_SESSION['loggedin']  = true;
        $_SESSION['username']  = $uUsuario;
        $_SESSION['iduser']    = $uId;
        $_SESSION['rol']       = $uCargo;
        $_SESSION['start']     = time();
        $_SESSION['expire']    = $_SESSION['start'] + (60 * 60);
        mysqli_close($conexion);
        echo "<script>self.location='../SCH_Calendar_SOP.php';</script>";
        exit();
    }
} else {
    mysqli_stmt_close($stmt);
}

mysqli_close($conexion);
echo "Usuario o Password estan incorrectos.";
echo "<br><a href='../index.php'>Volver a Intentarlo</a>";
