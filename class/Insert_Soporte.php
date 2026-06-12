<?php
require_once("funciones.php");
require_once("conexionBD.php");
$conexion = conectarse();
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer/src/Exception.php';
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';


// ────────────────────────────────────────────────────────────────────

// Recoger y sanitizar datos del formulario
$fechaSoporte    = $conexion->real_escape_string($_POST['fechaSoporte'] ?? '');
$idCliente       = (int)($_POST['idCliente']  ?? 0);
$timeIni         = $conexion->real_escape_string($_POST['timeIni']     ?? '');
$idSoporte       = (int)($_POST['idSoporte']  ?? 0);
$idUsuario       = (int)($_POST['idUsuario']  ?? 0);
$comentarioRaw   = $_POST['comentario'] ?? '';           // sin escapar → para el email
$comentario      = $conexion->real_escape_string($comentarioRaw); // escapado → para SQL
$timeFin         = $conexion->real_escape_string($_POST['timeFin'] ?? '');

if (!$fechaSoporte || !$idCliente || !$timeIni || !$idSoporte || !$idUsuario) {
    echo "<script>alert('Datos incompletos. Complete todos los campos.'); history.back();</script>";
    exit;
}

// Obtener nombre del técnico desde la BD (con fallback al hardcoded original)
$nombreTecnico = "Técnico #$idUsuario";
$stmt_tec = $conexion->prepare(
    "SELECT NOMBRES, APELLIDOS FROM ADM_USUARIO WHERE IDADM_USUARIO = ? LIMIT 1"
);
if ($stmt_tec) {
    $stmt_tec->bind_param("i", $idUsuario);
    $stmt_tec->execute();
    $rowTec = $stmt_tec->get_result()->fetch_assoc();
    $stmt_tec->close();
    if ($rowTec) {
        $nombreTecnico = trim($rowTec['NOMBRES'] . ' ' . $rowTec['APELLIDOS']);
    }
} else {
    // Fallback hardcodeado si la consulta falla
    if ($idUsuario == 7) $nombreTecnico = "Javier Varas";
}

// Tipo de soporte desde switch (tabla CAT_TIPO_SOPORTE no existe en este sistema)
switch ($idSoporte) {
    case 1:  $tipoSoporteTexto = 'Soporte Remoto'; break;
    case 2:  $tipoSoporteTexto = 'Presencial';      break;
    default: $tipoSoporteTexto = "Tipo #$idSoporte"; break;
}

// Verificar si el horario ya está ocupado
$stmt_valida = $conexion->prepare(
    "SELECT ID_CALENDARIO_SOPORTE FROM COTI_CALENDARIO
     WHERE FECHA_SOPORTE = ? AND HORA_INICIO = ? AND ESTADO = 'A'
     AND ESTADO_SOPORTE NOT IN ('Cancelada','Cancelado')"
);
$stmt_valida->bind_param("ss", $fechaSoporte, $timeIni);
$stmt_valida->execute();
$stmt_valida->store_result();

if ($stmt_valida->num_rows > 0) {
    $stmt_valida->close();
    echo "<script>alert('¡Ya existe un soporte en ese horario!'); window.location.href = '../SCH_Calendar_SOP.php';</script>";
    exit;
}
$stmt_valida->close();

// Insertar el soporte
$idUserSesion = $_SESSION['iduser'] ?? 0;
$stmt_insert = $conexion->prepare(
    "INSERT INTO COTI_CALENDARIO
        (ID_CLIENTE, ID_SOPORTE, ID_USUARIO, IDUSUARIO,
         FECHA_SOPORTE, HORA_INICIO, HORA_FIN, ESTADO_SOPORTE, COMENTARIO, ESTADO)
     VALUES (?, ?, ?, ?, ?, ?, ?, 'Pendiente', ?, 'A')"
);
$stmt_insert->bind_param("iiiissss",
    $idCliente, $idSoporte, $idUsuario, $idUserSesion,
    $fechaSoporte, $timeIni, $timeFin, $comentarioRaw
);

if (!$stmt_insert->execute()) {
    echo "<script>alert('Error al registrar: " . addslashes($stmt_insert->error) . "'); history.back();</script>";
    $stmt_insert->close();
    exit;
}
$stmt_insert->close();

// Obtener nombre + correo del cliente
$stmt_cli = $conexion->prepare(
    "SELECT NOMBRES, APELLIDOS, EMAIL FROM AG_PACIENTE WHERE IDPACIENTE = ? LIMIT 1"
);
$stmt_cli->bind_param("i", $idCliente);
$stmt_cli->execute();
$rowCli = $stmt_cli->get_result()->fetch_assoc();
$stmt_cli->close();

$nombreCliente = $rowCli ? trim($rowCli['NOMBRES'] . ' ' . $rowCli['APELLIDOS']) : 'Cliente';
$correoCliente = $rowCli['EMAIL'] ?? '';

// Formatear fecha en español
$fechaObj  = DateTime::createFromFormat('Y-m-d', $fechaSoporte);
$diasES    = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
$mesesES   = ['','enero','febrero','marzo','abril','mayo','junio',
              'julio','agosto','septiembre','octubre','noviembre','diciembre'];
$fechaBonita = $fechaObj
    ? $diasES[(int)$fechaObj->format('w')] . ', ' .
      (int)$fechaObj->format('j') . ' de ' .
      $mesesES[(int)$fechaObj->format('n')] . ' de ' .
      $fechaObj->format('Y')
    : $fechaSoporte;

// ── Construir email ──────────────────────────────────────────────────
$comentarioHtml = nl2br(htmlspecialchars($comentarioRaw));

$htmlBody = "
<!DOCTYPE html>
<html lang='es'>
<head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;background:#f0f4f8;font-family:Arial,sans-serif;'>
  <table width='100%' cellpadding='0' cellspacing='0' style='background:#f0f4f8;padding:30px 0;'>
    <tr><td align='center'>
      <table width='580' cellpadding='0' cellspacing='0'
             style='background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);'>

        <!-- Encabezado -->
        <tr>
          <td style='background:#1a3a5c;padding:28px 32px;text-align:center;'>
            <h1 style='color:#ffffff;margin:0;font-size:22px;'>Soporte Técnico Programado</h1>
            <p style='color:#a8c4e0;margin:6px 0 0;font-size:13px;'>Overclocking — Comprobante de Servicio</p>
          </td>
        </tr>

        <!-- Saludo -->
        <tr>
          <td style='padding:28px 32px 10px;'>
            <p style='font-size:15px;color:#333;margin:0;'>
              Estimado/a <strong>" . htmlspecialchars($nombreCliente) . "</strong>,
            </p>
            <p style='font-size:14px;color:#555;margin:10px 0 0;'>
              Le informamos que el servicio de soporte técnico ha sido programado. Aquí están los detalles:
            </p>
          </td>
        </tr>

        <!-- Detalles -->
        <tr>
          <td style='padding:16px 32px;'>
            <table width='100%' cellpadding='0' cellspacing='0'
                   style='background:#f0f6ff;border-radius:6px;border-left:4px solid #1a3a5c;'>
              <tr><td style='padding:16px 20px;'>
                <table width='100%' cellpadding='6' cellspacing='0' style='font-size:14px;color:#333;'>
                  <tr>
                    <td style='width:42%;color:#888;'>📅 Fecha</td>
                    <td><strong>" . htmlspecialchars($fechaBonita) . "</strong></td>
                  </tr>
                  <tr>
                    <td style='color:#888;'>🕐 Hora</td>
                    <td><strong>" . htmlspecialchars($timeIni) . " – " . htmlspecialchars($timeFin) . "</strong></td>
                  </tr>
                  <tr>
                    <td style='color:#888;'>🔧 Tipo de soporte</td>
                    <td><strong>" . htmlspecialchars($tipoSoporteTexto) . "</strong></td>
                  </tr>
                  <tr>
                    <td style='color:#888;'>👨‍💻 Técnico asignado</td>
                    <td><strong>" . htmlspecialchars($nombreTecnico) . "</strong></td>
                  </tr>
                </table>
              </td></tr>
            </table>
          </td>
        </tr>

        " . ($comentarioRaw ? "
        <!-- Resumen de actividades -->
        <tr>
          <td style='padding:4px 32px 16px;'>
            <p style='font-size:13px;font-weight:bold;color:#1a3a5c;margin:0 0 6px;'>
              📋 Resumen de actividades:
            </p>
            <div style='background:#f9f9f9;border-radius:4px;padding:12px 16px;font-size:13px;
                        color:#444;line-height:1.6;border:1px solid #e0e0e0;'>
              $comentarioHtml
            </div>
          </td>
        </tr>" : "") . "

        <!-- Información adicional -->
        <tr>
          <td style='padding:4px 32px 24px;'>
            <p style='font-size:13px;color:#777;margin:0;'>
              Este comprobante sirve como constancia del servicio prestado.<br>
              Para consultas o seguimiento, puede referirse al técnico
              <strong>" . htmlspecialchars($nombreTecnico) . "</strong>.
            </p>
          </td>
        </tr>

        <!-- Pie -->
        <tr>
          <td style='background:#e8f0f8;padding:16px 32px;text-align:center;'>
            <p style='margin:0;font-size:12px;color:#999;'>
              Generado automáticamente por el Sistema de Reportes Overclocking.<br>
              Por favor no responda a este mensaje.
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>";

$textBody = "Estimado/a $nombreCliente,\n\n"
          . "Soporte técnico programado:\n"
          . "Fecha: $fechaBonita\n"
          . "Hora: $timeIni - $timeFin\n"
          . "Tipo: $tipoSoporteTexto\n"
          . "Técnico: $nombreTecnico\n"
          . ($comentarioRaw ? "\nActividades:\n$comentarioRaw\n" : "")
          . "\nAtentamente,\nEquipo de Soporte Técnico — Overclocking";

// ── Enviar correo ────────────────────────────────────────────────────
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
    $mail->Timeout    = 10; // corta si no conecta en 10 seg
    $mail->CharSet    = 'UTF-8';
    $mail->Encoding   = 'base64';
    $mail->SMTPOptions = ['ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true,
    ]];

    $mail->setFrom('soporte@overclocking.com.ec', 'Sistema de reportes OVERCLOCKING');

    if ($correoCliente) {
        $mail->addAddress($correoCliente, $nombreCliente);
    }

    // CCs internos fijos
    $mail->addCC('janviervaras@hotmail.com');
    $mail->addCC('belensarmiento@inasar.ec');
    $mail->addCC('msarmiento@inasar.ec');
    $mail->addCC('soporte@inasar.ec');
    $mail->addCC('jvaras@overclocking.com.ec');

    $mail->addReplyTo('soporte@overclocking.com.ec', 'Overclocking Soporte');
    $mail->isHTML(true);
    $mail->Subject = '=?UTF-8?B?' . base64_encode('Soporte Técnico Programado - Overclocking') . '?=';
    $mail->Body    = $htmlBody;
    $mail->AltBody = $textBody;

    $mail->send();

} catch (Exception $e) {
    error_log("PHPMailer Overclocking: " . $e->getMessage());
}

// Siempre redirigir (la cita ya fue guardada aunque falle el correo)
echo "<script>window.location.href = '../SCH_Calendar_SOP.php';</script>";

$conexion->close();
?>