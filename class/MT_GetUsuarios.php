<?php
ob_start();
session_start();
require_once("funciones.php");
require_once("conexionBD_MT.php");
$con = conectarse_MT();

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "SUPERADMIN") {
    http_response_code(403);
    echo json_encode([]);
    exit();
}

$idEmpresa = isset($_GET['empresa']) ? (int)$_GET['empresa'] : 0;

if (!$idEmpresa) {
    echo json_encode([]);
    exit();
}

$stmt = mysqli_prepare($con,
    "SELECT u.IDADM_USUARIO, u.NOMBRES, u.APELLIDOS, u.USUARIO, u.TELEFONO, u.ESTADO, r.CARGO AS ROL
     FROM ADM_USUARIO u
     INNER JOIN ADM_ROL r ON u.IDADM_ROL = r.IDADM_ROL
     WHERE u.ID_EMPRESA = ?
     ORDER BY u.NOMBRES ASC"
);
mysqli_stmt_bind_param($stmt, "i", $idEmpresa);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$usuarios = [];
while ($row = mysqli_fetch_assoc($result)) {
    $usuarios[] = $row;
}
mysqli_stmt_close($stmt);
mysqli_close($con);

header("Content-Type: application/json");
echo json_encode($usuarios);
exit();
