<?php
require_once("funciones.php");
require_once("conexionBD.php");

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar que los datos no estén vacíos
    if (empty($_POST["idUsuarioRn"]) || empty($_POST["claveRn"])) {
        die("Error: Datos incompletos.");
    }

    $idUsuario = $_POST["idUsuarioRn"];
    $clave = $_POST['claveRn'];
    $cifrar = password_hash($clave, PASSWORD_DEFAULT);

    // Conectar a la base de datos
    $conexion = conectarse();
    if (!$conexion) {
        die("Error al conectar a la base de datos: " . mysqli_connect_error());
    }

    // Preparar la consulta SQL
    $sql = "UPDATE ADM_USUARIO SET CONTRASENA = ? WHERE IDADM_USUARIO = ?";
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        die("Error al preparar la consulta: " . $conexion->error);
    }

    // Vincular parámetros y ejecutar la consulta
    $stmt->bind_param("si", $cifrar, $idUsuario);
    if ($stmt->execute()) {
        echo "Clave actualizada correctamente";
    } else {
        echo "Error al actualizar: " . $stmt->error;
    }

    // Cerrar la declaración y la conexión
    $stmt->close();
    $conexion->close();
}
?>

