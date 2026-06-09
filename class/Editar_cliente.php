<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();
     
   
    
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $idCliente = $_POST["idCliente"];
    $identificacion = $_POST["identificacion"];
    $razon_social = $_POST["razon_social"];
    $telefono = $_POST["telefono"];
    $nombres = $_POST["nombres"];
    $apellidos = $_POST["apellidos"];
    $email = $_POST["email"];
    
   
   
    $sql = "UPDATE COTI_CLIENTE SET 
                IDENTIFICACION = ?,
                RAZON_SOCIAL = ?,
                CELULAR = ?,
                NOMBRES = ?, 
                APELLIDOS = ?, 
                CORREO = ?
            WHERE ID_CLIENTE = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssssi", $identificacion,$razon_social, $telefono, $nombres, $apellidos, $email, $idCliente);

    if ($stmt->execute()) {
        echo "Cliente actualizado correctamente";
    } else {
        echo "Error al actualizar: " . $stmt->error;
    }

    $stmt->close();
}


?>

