<?php
ob_start();
session_start();
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();

$idEquipo       = isset($_POST['idEquipo'])        ? (int)$_POST['idEquipo']                                   : null;
$fechaSalida    = isset($_POST['fechaSalida'])      ? mysqli_real_escape_string($conexion, $_POST['fechaSalida'])    : null;
$danioReportado = isset($_POST['danioReportado'])   ? mysqli_real_escape_string($conexion, $_POST['danioReportado']) : null;

if (!$idEquipo || !$fechaSalida || !$danioReportado) {
    header("Location: ../SCH_Calendar_SOP.php?error=datos_incompletos");
    exit();
}

$stmt = mysqli_prepare($conexion, "INSERT INTO INV_MANTENIMIENTOS (ID_EQUIPO, FECHA_SALIDA, DANIO_REPORTADO, ESTADO) VALUES (?, ?, ?, 'En Reparacion')");
mysqli_stmt_bind_param($stmt, "iss", $idEquipo, $fechaSalida, $danioReportado);

if (!mysqli_stmt_execute($stmt)) {
    header("Location: ../SCH_Calendar_SOP.php?error=" . urlencode(mysqli_error($conexion)));
    exit();
}
$idMantenimiento = mysqli_insert_id($conexion);
mysqli_stmt_close($stmt);

// Procesar evidencias fotográficas de la salida (opcional)
if (!empty($_FILES['evidenciasSalida']) && is_array($_FILES['evidenciasSalida']['name'])) {
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $evidencias = [];
    $totalArchivos = count($_FILES['evidenciasSalida']['name']);

    if (!is_dir(__DIR__ . '/../images/mantenimientos')) {
        mkdir(__DIR__ . '/../images/mantenimientos', 0755, true);
    }

    for ($i = 0; $i < $totalArchivos; $i++) {
        if ($_FILES['evidenciasSalida']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        $extension = strtolower(pathinfo($_FILES['evidenciasSalida']['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensionesPermitidas)) {
            continue;
        }
        $nombreArchivo = 'salida_' . $idMantenimiento . '_' . time() . '_' . $i . '.' . $extension;
        $rutaDestino = __DIR__ . '/../images/mantenimientos/' . $nombreArchivo;
        if (move_uploaded_file($_FILES['evidenciasSalida']['tmp_name'][$i], $rutaDestino)) {
            $evidencias[] = 'images/mantenimientos/' . $nombreArchivo;
        }
    }

    if (!empty($evidencias)) {
        $evidenciasGuardar = implode(',', $evidencias);
        $stmtEvi = mysqli_prepare($conexion, "UPDATE INV_MANTENIMIENTOS SET EVIDENCIAS_SALIDA = ? WHERE ID_MANTENIMIENTO = ?");
        mysqli_stmt_bind_param($stmtEvi, "si", $evidenciasGuardar, $idMantenimiento);
        mysqli_stmt_execute($stmtEvi);
        mysqli_stmt_close($stmtEvi);
    }
}

header("Location: ../SCH_Calendar_SOP.php?success=mantenimiento_registrado");
mysqli_close($conexion);
exit();
