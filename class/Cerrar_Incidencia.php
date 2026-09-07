<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

$idIncidencia = (int)($_POST['idIncidencia'] ?? 0);
$solucion     = mysqli_real_escape_string($conexion, trim($_POST['solucion'] ?? ''));

if (!$idIncidencia || !$solucion) {
    echo "<script>alert('Datos incompletos.'); history.back();</script>";
    exit();
}

// Procesar evidencias fotográficas de la solución (opcional)
$evidencias = [];
if (!empty($_FILES['evidenciasSolucion']) && is_array($_FILES['evidenciasSolucion']['name'])) {
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $totalArchivos = count($_FILES['evidenciasSolucion']['name']);

    if (!is_dir(__DIR__ . '/../images/cctv')) {
        mkdir(__DIR__ . '/../images/cctv', 0755, true);
    }

    for ($i = 0; $i < $totalArchivos; $i++) {
        if ($_FILES['evidenciasSolucion']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        $extension = strtolower(pathinfo($_FILES['evidenciasSolucion']['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensionesPermitidas)) {
            continue;
        }
        $nombreArchivo = 'solucion_' . $idIncidencia . '_' . time() . '_' . mt_rand(1000, 9999) . '_' . $i . '.' . $extension;
        $rutaDestino = __DIR__ . '/../images/cctv/' . $nombreArchivo;
        if (move_uploaded_file($_FILES['evidenciasSolucion']['tmp_name'][$i], $rutaDestino)) {
            $evidencias[] = 'images/cctv/' . $nombreArchivo;
        }
    }
}
$evidenciasGuardar = mysqli_real_escape_string($conexion, implode(',', $evidencias));

$sql = "UPDATE CCTV_INCIDENCIA SET ESTADO = 'Resuelta', SOLUCION = '$solucion', EVIDENCIAS_SOLUCION = '$evidenciasGuardar' WHERE ID_INCIDENCIA = $idIncidencia";

if ($conexion->query($sql) === TRUE) {
    echo "<script>alert('Incidencia marcada como resuelta.'); window.location.href = '../CCTV_Incidencias.php';</script>";
} else {
    echo "<script>alert('Error: " . addslashes($conexion->error) . "'); history.back();</script>";
}
$conexion->close();
?>
