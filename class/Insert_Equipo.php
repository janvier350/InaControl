<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

// Capturar datos del formulario
$dispositivo = $_POST['dispositivo'];
$marca = $_POST['marca'];
$modelo = $_POST['modelo'];
$procesador = $_POST['procesador'];
$hdd = $_POST['hdd'];
$serial = $_POST['serial'];
$ram = $_POST['ram'];
$pantalla = $_POST['pantalla'];
$observaciones = $_POST['observaciones'];
$fechaCompra = $_POST['fechaCompra'];
$departamento = $_POST['departamento'];
$dispositivo = $_POST['dispositivo'];

// Validar si el usuario ya existe
 $sqlValida = "SELECT * FROM INV_EQUIPO WHERE SERIAL = '$serial' AND ESTADO = 'A'";
 $result = $conexion->query($sqlValida);

if ($result->num_rows > 0) {
    echo "<script>alert('Equipo ya existe!'); window.location.href = '../INV_EQUIPO.php';</script>";
    exit(); // Salir del script para evitar la ejecuci贸n del INSERT
}

// Procesar imagen del equipo (opcional)
$imagen = '';
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));

    if (in_array($extension, $extensionesPermitidas)) {
        $nombreArchivo = 'equipo_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $serial) . '_' . time() . '.' . $extension;
        $rutaDestino = __DIR__ . '/../images/equipos/' . $nombreArchivo;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
            $imagen = 'images/equipos/' . $nombreArchivo;
        }
    }
}

// Insertar nuevo equipo
$sql = "INSERT INTO INV_EQUIPO (FECHA_COMPRA, DEPARTAMENTO, MARCA, MODELO, SERIAL, PROCESADOR, HDD, RAM, PANTALLA, OBSERVACIONES, ESTADO, DISPOSITIVO, ESTADO_AI, IMAGEN)
        VALUES ('$fechaCompra', '$departamento', '$marca', '$modelo', '$serial', '$procesador', '$hdd', '$ram', '$pantalla', '$observaciones' ,'A', '$dispositivo', 'A', '$imagen')";

if ($conexion->query($sql) === TRUE) {
    echo "<script>alert('Eqiupo Registrado Correctamente!'); window.location.href = '../INV_EQUIPO.php';</script>";
} else {
    echo "Error al insertar usuario: " . $conexion->error;
}
?>
