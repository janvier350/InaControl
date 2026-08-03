<?php
session_start();
require_once("conexionBD_MT.php");

if (!isset($_SESSION['id_empresa'])) {
    header("Location: ../EMPRESA_login.php?error=" . urlencode("Sesión inválida."));
    exit();
}

$con = conectarse_MT();
if (!$con) {
    echo "<script>alert('No se pudo conectar a la base de datos.'); history.back();</script>";
    exit();
}

$idEmpresa      = (int)$_SESSION['id_empresa'];
$fechaSoporte   = trim($_POST['fechaSoporte'] ?? '');
$idCliente      = (int)($_POST['idCliente'] ?? 0);
$timeIni        = trim($_POST['timeIni'] ?? '');
$idSoporte      = (int)($_POST['idSoporte'] ?? 0);
$idUsuario      = (int)($_POST['idUsuario'] ?? 0);
$comentario     = trim($_POST['comentario'] ?? '');
$timeFin        = trim($_POST['timeFin'] ?? '');

if (!$fechaSoporte || !$idCliente || !$timeIni || !$idSoporte || !$idUsuario) {
    echo "<script>alert('Datos incompletos. Complete todos los campos.'); history.back();</script>";
    exit();
}

// Verificar si el horario ya está ocupado (dentro de la misma empresa)
$stmtValida = mysqli_prepare($con,
    "SELECT ID_CALENDARIO_SOPORTE FROM COTI_CALENDARIO
     WHERE ID_EMPRESA = ? AND FECHA_SOPORTE = ? AND HORA_INICIO = ? AND ESTADO = 'A'
     AND ESTADO_SOPORTE NOT IN ('Cancelada','Cancelado')"
);
mysqli_stmt_bind_param($stmtValida, "iss", $idEmpresa, $fechaSoporte, $timeIni);
mysqli_stmt_execute($stmtValida);
mysqli_stmt_store_result($stmtValida);

if (mysqli_stmt_num_rows($stmtValida) > 0) {
    mysqli_stmt_close($stmtValida);
    echo "<script>alert('¡Ya existe un soporte en ese horario!'); window.location.href = '../MT_Calendar_SOP.php';</script>";
    exit();
}
mysqli_stmt_close($stmtValida);

$stmtInsert = mysqli_prepare($con,
    "INSERT INTO COTI_CALENDARIO
        (ID_EMPRESA, ID_CLIENTE, ID_SOPORTE, ID_USUARIO, FECHA_SOPORTE, HORA_INICIO, HORA_FIN, ESTADO_SOPORTE, COMENTARIO, ESTADO)
     VALUES (?, ?, ?, ?, ?, ?, ?, 'Pendiente', ?, 'A')"
);
mysqli_stmt_bind_param($stmtInsert, "iiiissss",
    $idEmpresa, $idCliente, $idSoporte, $idUsuario, $fechaSoporte, $timeIni, $timeFin, $comentario
);

if (!mysqli_stmt_execute($stmtInsert)) {
    echo "<script>alert('Error al registrar: " . addslashes(mysqli_error($con)) . "'); history.back();</script>";
    mysqli_stmt_close($stmtInsert);
    exit();
}
$idCalendario = mysqli_insert_id($con);
mysqli_stmt_close($stmtInsert);

// Procesar imágenes de evidencia (opcional)
if (!empty($_FILES['evidencias']) && is_array($_FILES['evidencias']['name'])) {
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $rutasEvidencias = [];
    $totalArchivos = count($_FILES['evidencias']['name']);

    for ($i = 0; $i < $totalArchivos; $i++) {
        if ($_FILES['evidencias']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        $extension = strtolower(pathinfo($_FILES['evidencias']['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensionesPermitidas)) {
            continue;
        }
        $nombreArchivo = 'mt_soporte_' . $idEmpresa . '_' . $idCalendario . '_' . time() . '_' . $i . '.' . $extension;
        $rutaDestino = __DIR__ . '/../images/evidencias/' . $nombreArchivo;

        if (move_uploaded_file($_FILES['evidencias']['tmp_name'][$i], $rutaDestino)) {
            $rutasEvidencias[] = 'images/evidencias/' . $nombreArchivo;
        }
    }

    if (!empty($rutasEvidencias)) {
        $evidenciasGuardar = implode(',', $rutasEvidencias);
        $stmtEvi = mysqli_prepare($con, "UPDATE COTI_CALENDARIO SET EVIDENCIAS = ? WHERE ID_CALENDARIO_SOPORTE = ?");
        mysqli_stmt_bind_param($stmtEvi, "si", $evidenciasGuardar, $idCalendario);
        mysqli_stmt_execute($stmtEvi);
        mysqli_stmt_close($stmtEvi);
    }
}

mysqli_close($con);
echo "<script>window.location.href = '../MT_Calendar_SOP.php';</script>";
