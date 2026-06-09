<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$id = isset($_POST['id']) ? $_POST['id'] : null;
$estado = isset($_POST['estado']) ? $_POST['estado'] : null;

if ($id === null || $estado === null) {
    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos"
    ]);
    exit();
}

// Verifica si el ID existe en la base de datos
$verificar = $conexion->prepare("SELECT 1 FROM COTI_CALENDARIO WHERE ID_CALENDARIO_SOPORTE = ?");
$verificar->bind_param("i", $id);
$verificar->execute();
$resultado = $verificar->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "No se encontró la cita con el ID proporcionado"
    ]);
    exit();
}

// Intentar actualizar el estado de la cita
$stmt = $conexion->prepare("UPDATE COTI_CALENDARIO SET ESTADO_SOPORTE = ? WHERE ID_CALENDARIO_SOPORTE = ?");
$stmt->bind_param("si", $estado, $id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Estado actualizado correctamente"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Error al actualizar",
        "error" => $stmt->error
    ]);
}

$stmt->close();
$conexion->close();
?>
