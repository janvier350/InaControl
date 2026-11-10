<?php
ob_start();
session_start();
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();

$idMantenimiento  = isset($_POST['idMantenimiento'])  ? (int)$_POST['idMantenimiento']  : null;
$danioReportado   = isset($_POST['danioReportado'])   ? mysqli_real_escape_string($conexion, $_POST['danioReportado'])   : null;
$solucionAplicada = isset($_POST['solucionAplicada']) ? mysqli_real_escape_string($conexion, $_POST['solucionAplicada']) : '';
$estado           = ($_POST['estado'] ?? 'En Reparacion') === 'Completado' ? 'Completado' : 'En Reparacion';

if (!$idMantenimiento || !$danioReportado) {
    echo "<script>alert('Datos incompletos.'); history.back();</script>";
    exit();
}

// Evidencias actuales guardadas en BD
$stmtGet = mysqli_prepare($conexion, "SELECT EVIDENCIAS_SALIDA, EVIDENCIAS_ENTREGA FROM INV_MANTENIMIENTOS WHERE ID_MANTENIMIENTO = ?");
mysqli_stmt_bind_param($stmtGet, "i", $idMantenimiento);
mysqli_stmt_execute($stmtGet);
$resGet = mysqli_stmt_get_result($stmtGet);
$rowActual = mysqli_fetch_assoc($resGet);
mysqli_stmt_close($stmtGet);

if (!$rowActual) {
    echo "<script>alert('Mantenimiento no encontrado.'); history.back();</script>";
    exit();
}

function procesarEvidenciasMant($conexion, $actualCsv, $campoEliminarPost, $campoArchivosPost, $prefijoArchivo, $idMantenimiento) {
    $actuales = !empty($actualCsv) ? explode(',', $actualCsv) : [];

    $aEliminar = [];
    if (!empty($_POST[$campoEliminarPost])) {
        $decoded = json_decode($_POST[$campoEliminarPost], true);
        if (is_array($decoded)) { $aEliminar = $decoded; }
    }

    $final = [];
    foreach ($actuales as $ruta) {
        if (in_array($ruta, $aEliminar, true)) {
            $rutaAbsoluta = __DIR__ . '/../' . $ruta;
            if (strpos($ruta, 'images/mantenimientos/') === 0 && file_exists($rutaAbsoluta)) {
                @unlink($rutaAbsoluta);
            }
        } else {
            $final[] = $ruta;
        }
    }

    if (!empty($_FILES[$campoArchivosPost]) && is_array($_FILES[$campoArchivosPost]['name'])) {
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $totalArchivos = count($_FILES[$campoArchivosPost]['name']);

        if (!is_dir(__DIR__ . '/../images/mantenimientos')) {
            mkdir(__DIR__ . '/../images/mantenimientos', 0755, true);
        }

        for ($i = 0; $i < $totalArchivos; $i++) {
            if ($_FILES[$campoArchivosPost]['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            $extension = strtolower(pathinfo($_FILES[$campoArchivosPost]['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($extension, $extensionesPermitidas)) {
                continue;
            }
            $nombreArchivo = $prefijoArchivo . '_' . $idMantenimiento . '_' . time() . '_' . $i . '.' . $extension;
            $rutaDestino = __DIR__ . '/../images/mantenimientos/' . $nombreArchivo;
            if (move_uploaded_file($_FILES[$campoArchivosPost]['tmp_name'][$i], $rutaDestino)) {
                $final[] = 'images/mantenimientos/' . $nombreArchivo;
            }
        }
    }

    return mysqli_real_escape_string($conexion, implode(',', $final));
}

$evidenciasSalidaFinal  = procesarEvidenciasMant($conexion, $rowActual['EVIDENCIAS_SALIDA'],  'evidenciasSalidaEliminar',  'evidenciasSalidaNuevas',  'salida',  $idMantenimiento);
$evidenciasEntregaFinal = procesarEvidenciasMant($conexion, $rowActual['EVIDENCIAS_ENTREGA'], 'evidenciasEntregaEliminar', 'evidenciasEntregaNuevas', 'entrega', $idMantenimiento);

$stmt = mysqli_prepare($conexion, "UPDATE INV_MANTENIMIENTOS SET DANIO_REPORTADO = ?, SOLUCION_APLICADA = ?, ESTADO = ?, EVIDENCIAS_SALIDA = ?, EVIDENCIAS_ENTREGA = ? WHERE ID_MANTENIMIENTO = ?");
mysqli_stmt_bind_param($stmt, "sssssi", $danioReportado, $solucionAplicada, $estado, $evidenciasSalidaFinal, $evidenciasEntregaFinal, $idMantenimiento);

if (mysqli_stmt_execute($stmt)) {
    echo "<script>alert('Mantenimiento actualizado correctamente.'); window.location.href = '../INV_EQUIPO.php#tab-content-2';</script>";
} else {
    echo "<script>alert('Error al actualizar: " . addslashes(mysqli_error($conexion)) . "'); history.back();</script>";
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>
