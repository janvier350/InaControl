<?php
ob_start();
session_start();
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();

$id     = isset($_POST['id'])     ? (int)$_POST['id']                              : null;
$estado = isset($_POST['estado']) ? $conexion->real_escape_string($_POST['estado']) : null;

if ($id === null || $estado === null) {
    header("Location: ../SCH_Calendar_SOP.php?error=datos_incompletos");
    exit();
}

$verificar = $conexion->prepare("SELECT ID_CALENDARIO_SOPORTE FROM COTI_CALENDARIO WHERE ID_CALENDARIO_SOPORTE = ?");
$verificar->bind_param("i", $id);
$verificar->execute();
$res = $verificar->get_result();

if ($res->num_rows === 0) {
    header("Location: ../SCH_Calendar_SOP.php?error=cita_no_encontrada");
    exit();
}

$stmt = $conexion->prepare("UPDATE COTI_CALENDARIO SET ESTADO_SOPORTE = ? WHERE ID_CALENDARIO_SOPORTE = ?");
$stmt->bind_param("si", $estado, $id);

if ($stmt->execute()) {
    header("Location: ../SCH_Calendar_SOP.php?success=1");
} else {
    header("Location: ../SCH_Calendar_SOP.php?error=" . urlencode($stmt->error));
}

$stmt->close();
$conexion->close();
exit();
