<?php
ob_start();
session_start();
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();

$idMantenimiento  = isset($_POST['idMantenimiento'])  ? (int)$_POST['idMantenimiento']                                    : null;
$fechaEntrega     = isset($_POST['fechaEntrega'])     ? mysqli_real_escape_string($conexion, $_POST['fechaEntrega'])      : null;
$solucionAplicada = isset($_POST['solucionAplicada']) ? mysqli_real_escape_string($conexion, $_POST['solucionAplicada'])  : null;

if (!$idMantenimiento || !$fechaEntrega || !$solucionAplicada) {
    header("Location: ../SCH_Calendar_SOP.php?error=datos_incompletos");
    exit();
}

$verificar = mysqli_prepare($conexion, "SELECT ID_MANTENIMIENTO FROM INV_MANTENIMIENTOS WHERE ID_MANTENIMIENTO = ? AND ESTADO = 'En Reparacion'");
mysqli_stmt_bind_param($verificar, "i", $idMantenimiento);
mysqli_stmt_execute($verificar);
$res = mysqli_stmt_get_result($verificar);

if (mysqli_num_rows($res) === 0) {
    header("Location: ../SCH_Calendar_SOP.php?error=mantenimiento_no_encontrado");
    exit();
}
mysqli_stmt_close($verificar);

// Procesar evidencias fotográficas de la entrega (opcional)
$evidenciasGuardar = '';
if (!empty($_FILES['evidenciasEntrega']) && is_array($_FILES['evidenciasEntrega']['name'])) {
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $evidencias = [];
    $totalArchivos = count($_FILES['evidenciasEntrega']['name']);

    if (!is_dir(__DIR__ . '/../images/mantenimientos')) {
        mkdir(__DIR__ . '/../images/mantenimientos', 0755, true);
    }

    for ($i = 0; $i < $totalArchivos; $i++) {
        if ($_FILES['evidenciasEntrega']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        $extension = strtolower(pathinfo($_FILES['evidenciasEntrega']['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensionesPermitidas)) {
            continue;
        }
        $nombreArchivo = 'entrega_' . $idMantenimiento . '_' . time() . '_' . $i . '.' . $extension;
        $rutaDestino = __DIR__ . '/../images/mantenimientos/' . $nombreArchivo;
        if (move_uploaded_file($_FILES['evidenciasEntrega']['tmp_name'][$i], $rutaDestino)) {
            $evidencias[] = 'images/mantenimientos/' . $nombreArchivo;
        }
    }
    $evidenciasGuardar = mysqli_real_escape_string($conexion, implode(',', $evidencias));
}

$stmt = mysqli_prepare($conexion, "UPDATE INV_MANTENIMIENTOS SET FECHA_ENTREGA = ?, SOLUCION_APLICADA = ?, EVIDENCIAS_ENTREGA = ?, ESTADO = 'Completado' WHERE ID_MANTENIMIENTO = ?");
mysqli_stmt_bind_param($stmt, "sssi", $fechaEntrega, $solucionAplicada, $evidenciasGuardar, $idMantenimiento);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../SCH_Calendar_SOP.php?success=mantenimiento_cerrado");
} else {
    header("Location: ../SCH_Calendar_SOP.php?error=" . urlencode(mysqli_error($conexion)));
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
exit();
