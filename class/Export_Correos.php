<?php
ob_start();
session_start();
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();

if (!isset($_SESSION["rol"])) {
    header("Location: ../break.php");
    exit();
}

$sqlC = "SELECT C.CORREO, C.ALMACENAMIENTO, C.DEPARTAMENTO, C.ESTADO,
                DATE_FORMAT(C.FECHA_REGISTRO, '%d/%m/%Y') AS FECHA,
                IFNULL(CONCAT(U.NOMBRES,' ',U.APELLIDOS), 'Sin asignar') AS ASIGNADO_A
         FROM COR_CORREO C
         LEFT JOIN ADM_USUARIO U ON C.IDADM_USUARIO = U.IDADM_USUARIO
         ORDER BY C.CORREO ASC";
$qC = $conexion->query($sqlC);

ob_end_clean();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="correos_corporativos_' . date('Ymd') . '.csv"');
header('Pragma: no-cache');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 para Excel

fputcsv($out, array('CORREO', 'DEPARTAMENTO', 'ALMACENAMIENTO', 'ASIGNADO A', 'ESTADO', 'FECHA REGISTRO'), ';');

if ($qC) {
    while ($c = mysqli_fetch_array($qC)) {
        fputcsv($out, array(
            $c['CORREO'],
            $c['DEPARTAMENTO'] ?: '',
            $c['ALMACENAMIENTO'] ?: '',
            $c['ASIGNADO_A'],
            $c['ESTADO'] === 'A' ? 'Activo' : 'Inactivo',
            $c['FECHA'],
        ), ';');
    }
}

fclose($out);
mysqli_close($conexion);
exit();
