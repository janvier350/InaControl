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

$id = isset($_POST['id']) ? $_POST['id'] : null;
$estado = isset($_POST['estado']) ? $_POST['estado'] : null;

if ($id === null || $estado === null) {
    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos",
        "id_recibido" => $id,
        "estado_recibido" => $estado
    ]);
    exit();
}

// Verifica si el ID existe en la base de datos y recupera sus datos
$verificar = $conexion->prepare(
    "SELECT A.*, CONCAT(D.NOMBRES, ' ', D.APELLIDOS) AS TECNICO, C.SOPORTE AS TIPO_SOPORTE
     FROM COTI_CALENDARIO A
     INNER JOIN ADM_USUARIO D ON A.ID_USUARIO = D.IDADM_USUARIO
     INNER JOIN COTI_TIPO_SOPORTE C ON A.ID_SOPORTE = C.ID_TIPO_SOPORTE
     WHERE A.ID_CALENDARIO_SOPORTE = ?"
);
$verificar->bind_param("i", $id);
$verificar->execute();
$resultado = $verificar->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "No se encontró la cita con ID proporcionado",
        "id_recibido" => $id
    ]);
    exit();
}

$cita = $resultado->fetch_assoc();

// Procesar imágenes de evidencia (opcional)
$rutasEvidencias = [];
if (!empty($_FILES['evidencias']) && is_array($_FILES['evidencias']['name'])) {
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $totalArchivos = count($_FILES['evidencias']['name']);

    for ($i = 0; $i < $totalArchivos; $i++) {
        if ($_FILES['evidencias']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        $extension = strtolower(pathinfo($_FILES['evidencias']['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensionesPermitidas)) {
            continue;
        }

        $nombreArchivo = 'soporte_' . $id . '_' . time() . '_' . $i . '.' . $extension;
        $rutaDestino = __DIR__ . '/../images/evidencias/' . $nombreArchivo;

        if (move_uploaded_file($_FILES['evidencias']['tmp_name'][$i], $rutaDestino)) {
            $rutasEvidencias[] = 'images/evidencias/' . $nombreArchivo;
        }
    }
}

// Combinar evidencias nuevas con las existentes
$evidenciasExistentes = !empty($cita['EVIDENCIAS']) ? explode(',', $cita['EVIDENCIAS']) : [];
$todasLasEvidencias = array_merge($evidenciasExistentes, $rutasEvidencias);
$evidenciasGuardar = implode(',', $todasLasEvidencias);

// Intentar actualizar el estado de la cita
$stmt = $conexion->prepare("UPDATE COTI_CALENDARIO SET ESTADO_SOPORTE = ?, EVIDENCIAS = ? WHERE ID_CALENDARIO_SOPORTE = ?");
$stmt->bind_param("ssi", $estado, $evidenciasGuardar, $id);

if ($stmt->execute()) {

    // Enviar correo con evidencias cuando el soporte se confirma con imágenes adjuntas
    if ($estado === 'Confirmada' && !empty($rutasEvidencias)) {

        // Obtener nombre + correo del cliente
        $stmt_cli = $conexion->prepare(
            "SELECT NOMBRES, APELLIDOS, EMAIL FROM AG_PACIENTE WHERE IDPACIENTE = ? LIMIT 1"
        );
        $stmt_cli->bind_param("i", $cita['ID_CLIENTE']);
        $stmt_cli->execute();
        $rowCli = $stmt_cli->get_result()->fetch_assoc();
        $stmt_cli->close();

        $nombreCliente = $rowCli ? trim($rowCli['NOMBRES'] . ' ' . $rowCli['APELLIDOS']) : 'Cliente';
        $correoCliente = $rowCli['EMAIL'] ?? '';

        // Formatear fecha en español
        $fechaObj  = DateTime::createFromFormat('Y-m-d', $cita['FECHA_SOPORTE']);
        $diasES    = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
        $mesesES   = ['','enero','febrero','marzo','abril','mayo','junio',
                      'julio','agosto','septiembre','octubre','noviembre','diciembre'];
        $fechaBonita = $fechaObj
            ? $diasES[(int)$fechaObj->format('w')] . ', ' .
              (int)$fechaObj->format('j') . ' de ' .
              $mesesES[(int)$fechaObj->format('n')] . ' de ' .
              $fechaObj->format('Y')
            : $cita['FECHA_SOPORTE'];

        $comentarioHtml = nl2br(htmlspecialchars($cita['COMENTARIO'] ?? ''));

        $htmlBody = "
        <!DOCTYPE html>
        <html lang='es'>
        <head><meta charset='UTF-8'></head>
        <body style='margin:0;padding:0;background:#f0f4f8;font-family:Arial,sans-serif;'>
          <table width='100%' cellpadding='0' cellspacing='0' style='background:#f0f4f8;padding:30px 0;'>
            <tr><td align='center'>
              <table width='580' cellpadding='0' cellspacing='0'
                     style='background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);'>

                <tr>
                  <td style='background:#1a3a5c;padding:28px 32px;text-align:center;'>
                    <h1 style='color:#ffffff;margin:0;font-size:22px;'>Soporte Técnico Finalizado</h1>
                    <p style='color:#a8c4e0;margin:6px 0 0;font-size:13px;'>Overclocking — Evidencias del Servicio</p>
                  </td>
                </tr>

                <tr>
                  <td style='padding:28px 32px 10px;'>
                    <p style='font-size:15px;color:#333;margin:0;'>
                      Estimado/a <strong>" . htmlspecialchars($nombreCliente) . "</strong>,
                    </p>
                    <p style='font-size:14px;color:#555;margin:10px 0 0;'>
                      Le informamos que el servicio de soporte técnico ha sido confirmado/finalizado. Adjuntamos las evidencias del trabajo realizado.
                    </p>
                  </td>
                </tr>

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
                            <td><strong>" . htmlspecialchars($cita['HORA_INICIO']) . " – " . htmlspecialchars($cita['HORA_FIN']) . "</strong></td>
                          </tr>
                          <tr>
                            <td style='color:#888;'>🔧 Tipo de soporte</td>
                            <td><strong>" . htmlspecialchars($cita['TIPO_SOPORTE']) . "</strong></td>
                          </tr>
                          <tr>
                            <td style='color:#888;'>👨‍💻 Técnico asignado</td>
                            <td><strong>" . htmlspecialchars($cita['TECNICO']) . "</strong></td>
                          </tr>
                        </table>
                      </td></tr>
                    </table>
                  </td>
                </tr>

                " . ($cita['COMENTARIO'] ? "
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

                <tr>
                  <td style='padding:4px 32px 24px;'>
                    <p style='font-size:13px;font-weight:bold;color:#1a3a5c;margin:0 0 6px;'>
                      📷 Evidencias adjuntas: " . count($rutasEvidencias) . "
                    </p>
                    <p style='font-size:13px;color:#777;margin:0;'>
                      Las imágenes de evidencia del trabajo realizado se incluyen como archivos adjuntos en este correo.
                    </p>
                  </td>
                </tr>

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
                  . "Soporte técnico confirmado/finalizado:\n"
                  . "Fecha: $fechaBonita\n"
                  . "Hora: " . $cita['HORA_INICIO'] . " - " . $cita['HORA_FIN'] . "\n"
                  . "Tipo: " . $cita['TIPO_SOPORTE'] . "\n"
                  . "Técnico: " . $cita['TECNICO'] . "\n"
                  . (!empty($cita['COMENTARIO']) ? "\nActividades:\n" . $cita['COMENTARIO'] . "\n" : "")
                  . "\nSe adjuntan " . count($rutasEvidencias) . " imagen(es) de evidencia del trabajo realizado.\n"
                  . "\nAtentamente,\nEquipo de Soporte Técnico — Overclocking";

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
            $mail->Subject = '=?UTF-8?B?' . base64_encode('Evidencias de Soporte Técnico - Overclocking') . '?=';
            $mail->Body    = $htmlBody;
            $mail->AltBody = $textBody;

            foreach ($rutasEvidencias as $rutaEvidencia) {
                $mail->addAttachment(__DIR__ . '/../' . $rutaEvidencia);
            }

            $mail->send();

        } catch (Exception $e) {
            error_log("PHPMailer Overclocking (evidencias): " . $e->getMessage());
        }
    }

    echo "<Script language='JavaScript'>";
    echo 'self.location = "../SCH_Calendar_SOP.php"';
    echo "</script>";
} else {
    echo json_encode([
        "success" => false,
        "message" => "Error en la consulta SQL",
        "error" => $stmt->error
    ]);
}

$stmt->close();
$conexion->close();
?>
