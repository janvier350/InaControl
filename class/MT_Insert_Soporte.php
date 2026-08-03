<?php
session_start();
require_once("conexionBD_MT.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer/src/Exception.php';
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';

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

// Obtener datos del cliente y de la empresa para el correo
$stmtCli = mysqli_prepare($con, "SELECT NOMBRES, APELLIDOS, RAZON_SOCIAL, EMAIL FROM COTI_CLIENTE WHERE ID_CLIENTE = ? AND ID_EMPRESA = ?");
mysqli_stmt_bind_param($stmtCli, "ii", $idCliente, $idEmpresa);
mysqli_stmt_execute($stmtCli);
$cli = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCli));
mysqli_stmt_close($stmtCli);

$stmtEmp = mysqli_prepare($con, "SELECT NOMBRE, EMAIL_CONTACTO FROM EMPRESA WHERE ID_EMPRESA = ?");
mysqli_stmt_bind_param($stmtEmp, "i", $idEmpresa);
mysqli_stmt_execute($stmtEmp);
$emp = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtEmp));
mysqli_stmt_close($stmtEmp);

$stmtTipo = mysqli_prepare($con, "SELECT SOPORTE FROM COTI_TIPO_SOPORTE WHERE ID_TIPO_SOPORTE = ?");
mysqli_stmt_bind_param($stmtTipo, "i", $idSoporte);
mysqli_stmt_execute($stmtTipo);
$tipoRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTipo));
mysqli_stmt_close($stmtTipo);

$stmtTec = mysqli_prepare($con, "SELECT NOMBRES, APELLIDOS FROM ADM_USUARIO WHERE IDADM_USUARIO = ?");
mysqli_stmt_bind_param($stmtTec, "i", $idUsuario);
mysqli_stmt_execute($stmtTec);
$tecRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTec));
mysqli_stmt_close($stmtTec);

mysqli_close($con);

$nombreCliente  = $cli ? trim($cli['NOMBRES'].' '.$cli['APELLIDOS'].' '.$cli['RAZON_SOCIAL']) : 'Cliente';
$correoCliente  = $cli['EMAIL'] ?? '';
$nombreEmpresa  = $emp['NOMBRE'] ?? 'Empresa';
$correoContacto = $emp['EMAIL_CONTACTO'] ?? '';
$tipoSoporteTexto = $tipoRow['SOPORTE'] ?? 'Soporte';
$nombreTecnico  = $tecRow ? trim($tecRow['NOMBRES'].' '.$tecRow['APELLIDOS']) : 'Técnico';

$fechaObj  = DateTime::createFromFormat('Y-m-d', $fechaSoporte);
$diasES    = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
$mesesES   = ['','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$fechaBonita = $fechaObj
    ? $diasES[(int)$fechaObj->format('w')] . ', ' . (int)$fechaObj->format('j') . ' de ' . $mesesES[(int)$fechaObj->format('n')] . ' de ' . $fechaObj->format('Y')
    : $fechaSoporte;

if ($correoCliente || $correoContacto) {
    $comentarioHtml = nl2br(htmlspecialchars($comentario));
    $htmlBody = "
    <!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'></head>
    <body style='margin:0;padding:0;background:#f0f4f8;font-family:Arial,sans-serif;'>
      <table width='100%' cellpadding='0' cellspacing='0' style='background:#f0f4f8;padding:30px 0;'>
        <tr><td align='center'>
          <table width='580' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);'>
            <tr><td style='background:#0f3460;padding:28px 32px;text-align:center;'>
              <h1 style='color:#ffffff;margin:0;font-size:22px;'>Soporte Técnico Programado</h1>
              <p style='color:#a8c4e0;margin:6px 0 0;font-size:13px;'>" . htmlspecialchars($nombreEmpresa) . " — Comprobante de Servicio</p>
            </td></tr>
            <tr><td style='padding:28px 32px 10px;'>
              <p style='font-size:15px;color:#333;margin:0;'>Estimado/a <strong>" . htmlspecialchars($nombreCliente) . "</strong>,</p>
              <p style='font-size:14px;color:#555;margin:10px 0 0;'>Le informamos que el servicio de soporte técnico ha sido programado:</p>
            </td></tr>
            <tr><td style='padding:16px 32px;'>
              <table width='100%' cellpadding='0' cellspacing='0' style='background:#f0f6ff;border-radius:6px;border-left:4px solid #0f3460;'>
                <tr><td style='padding:16px 20px;'>
                  <table width='100%' cellpadding='6' cellspacing='0' style='font-size:14px;color:#333;'>
                    <tr><td style='width:42%;color:#888;'>Fecha</td><td><strong>" . htmlspecialchars($fechaBonita) . "</strong></td></tr>
                    <tr><td style='color:#888;'>Hora</td><td><strong>" . htmlspecialchars($timeIni) . " – " . htmlspecialchars($timeFin) . "</strong></td></tr>
                    <tr><td style='color:#888;'>Tipo de soporte</td><td><strong>" . htmlspecialchars($tipoSoporteTexto) . "</strong></td></tr>
                    <tr><td style='color:#888;'>Técnico asignado</td><td><strong>" . htmlspecialchars($nombreTecnico) . "</strong></td></tr>
                  </table>
                </td></tr>
              </table>
            </td></tr>" .
            ($comentario ? "
            <tr><td style='padding:4px 32px 16px;'>
              <p style='font-size:13px;font-weight:bold;color:#0f3460;margin:0 0 6px;'>Resumen de actividades:</p>
              <div style='background:#f9f9f9;border-radius:4px;padding:12px 16px;font-size:13px;color:#444;line-height:1.6;border:1px solid #e0e0e0;'>$comentarioHtml</div>
            </td></tr>" : "") . "
            <tr><td style='background:#e8f0f8;padding:16px 32px;text-align:center;'>
              <p style='margin:0;font-size:12px;color:#999;'>Generado automáticamente. Por favor no responda a este mensaje.</p>
            </td></tr>
          </table>
        </td></tr>
      </table>
    </body></html>";

    $textBody = "Estimado/a $nombreCliente,\n\nSoporte técnico programado:\nFecha: $fechaBonita\nHora: $timeIni - $timeFin\nTipo: $tipoSoporteTexto\nTécnico: $nombreTecnico\n"
              . ($comentario ? "\nActividades:\n$comentario\n" : "");

    $mail = new PHPMailer(true);
    try {
        $mailConfig = require __DIR__ . '/mail_config.php';
        $mail->isSMTP();
        $mail->Host       = $mailConfig['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailConfig['username'];
        $mail->Password   = $mailConfig['pass'];
        $mail->SMTPSecure = $mailConfig['secure'];
        $mail->Port       = $mailConfig['port'];
        $mail->Timeout    = 10;
        $mail->CharSet    = 'UTF-8';
        $mail->Encoding   = 'base64';
        $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];

        $mail->setFrom('soporte@overclocking.com.ec', $nombreEmpresa);

        if ($correoCliente) {
            $mail->addAddress($correoCliente, $nombreCliente);
        }
        if ($correoContacto && $correoContacto !== $correoCliente) {
            $mail->addCC($correoContacto);
        }
        if (!$correoCliente && !$correoContacto) {
            throw new Exception('Sin destinatarios configurados.');
        }

        $mail->addReplyTo('soporte@overclocking.com.ec', $nombreEmpresa);
        $mail->isHTML(true);
        $mail->Subject = '=?UTF-8?B?' . base64_encode('Soporte Técnico Programado - ' . $nombreEmpresa) . '?=';
        $mail->Body    = $htmlBody;
        $mail->AltBody = $textBody;

        $mail->send();
    } catch (Exception $e) {
        error_log("PHPMailer MT: " . $e->getMessage());
    }
}

echo "<script>window.location.href = '../MT_Calendar_SOP.php';</script>";
