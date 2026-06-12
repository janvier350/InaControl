<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idEquipo      = $_POST["idEquipo"];
    $dispositivo   = $_POST["dispositivo"];
    $marca         = $_POST["marca"];
    $modelo        = $_POST["modelo"];
    $procesador    = $_POST["procesador"];
    $hdd           = $_POST["hdd"];
    $serial        = $_POST["serial"];
    $ram           = $_POST["ram"];
    $pantalla      = $_POST["pantalla"];
    $observaciones = $_POST["observaciones"];
    $fechaCompra   = $_POST["fechaCompra"];
    $departamento  = $_POST["departamento"];
    $estado        = $_POST["estado"];

    $sql = "UPDATE INV_EQUIPO SET
                DISPOSITIVO = ?,
                MARCA = ?,
                MODELO = ?,
                PROCESADOR = ?,
                HDD = ?,
                SERIAL = ?,
                RAM = ?,
                PANTALLA = ?,
                OBSERVACIONES = ?,
                FECHA_COMPRA = ?,
                DEPARTAMENTO = ?,
                ESTADO = ?
            WHERE ID_EQUIPO = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(
        "ssssssssssssi",
        $dispositivo, $marca, $modelo, $procesador, $hdd, $serial,
        $ram, $pantalla, $observaciones, $fechaCompra, $departamento, $estado,
        $idEquipo
    );

    if ($stmt->execute()) {
        echo "Equipo actualizado correctamente";
    } else {
        echo "Error al actualizar: " . $stmt->error;
    }

    $stmt->close();
}
?>
