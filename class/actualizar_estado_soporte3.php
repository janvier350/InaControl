<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

// Forzar cabecera JSON
header('Content-Type: application/json');

$id = $_POST['id'] ?? null;
$estado = $_POST['estado'] ?? null;

// Validación de datos
if (!$id || !$estado) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos",
        "received_data" => $_POST
    ]);
    exit();
}

try {
    // Verificar existencia del registro
    $verificar = $conexion->prepare("SELECT 1 FROM COTI_CALENDARIO WHERE ID_CALENDARIO_SOPORTE = ?");
    $verificar->bind_param("i", $id);
    $verificar->execute();
    
    if (!$verificar->get_result()->num_rows) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Cita no encontrada"
        ]);
        exit();
    }

    // Actualizar estado
    $stmt = $conexion->prepare("UPDATE COTI_CALENDARIO SET ESTADO_SOPORTE = ? WHERE ID_CALENDARIO_SOPORTE = ?");
    $stmt->bind_param("si", $estado, $id);

    if ($stmt->execute()) {
        // Respuesta exitosa con redirección vía JavaScript
        echo json_encode([
            "success" => true,
            "message" => "Estado actualizado correctamente",
            "redirect" => "../SCH_Calendar_SOP.php"
        ]);
    } else {
        throw new Exception("Error en la actualización: " . $stmt->error);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error en el servidor: " . $e->getMessage()
    ]);
} finally {
    // Cerrar conexiones
    $verificar->close();
    $stmt->close();
    $conexion->close();
    exit();
}