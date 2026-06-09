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

// Recoger datos del formulario
$fechaSoporte = $_POST['fechaSoporte'];
$idCliente = $_POST['idCliente'];
$timeIni = $_POST['timeIni'];
$idSoporte = $_POST['idSoporte'];
$idUsuario = $_POST['idUsuario'];
$comentario = $_POST['comentario'];
$timeFin = $_POST['timeFin'];

// --- SECCIÓN DE TRADUCCIÓN DE CÓDIGOS PARA EL CORREO ---

// Traducción del Técnico (idUsuario)
if ($idUsuario == 7) {
    $nombreTecnico = "Javier Varas";
} else {
    $nombreTecnico = "Código técnico: " . $idUsuario;
}

// Traducción del Tipo de Soporte (idSoporte)
switch ($idSoporte) {
    case 1:
        $tipoSoporteTexto = "Soporte Remoto";
        break;
    case 2:
        $tipoSoporteTexto = "Presencial";
        break;
    default:
        $tipoSoporteTexto = "Código de soporte: " . $idSoporte;
        break;
}

// -------------------------------------------------------
$existe = false;

$sqlValida = "SELECT * FROM COTI_CALENDARIO 
              WHERE FECHA_SOPORTE = ? 
              AND HORA_INICIO = ? 
              AND estado = 'A'";

$stmt_valida = $conexion->prepare($sqlValida);
if (!$stmt_valida) {
    die("Error al preparar la consulta de validación: " . $conexion->error);
}

// Nota: Asegúrate que $fechafactura esté definida o usa $fechaSoporte aquí
$stmt_valida->bind_param("ss", $fechaSoporte, $timeIni);
$stmt_valida->execute();
$result_valida = $stmt_valida->get_result();

if ($result_valida->num_rows > 0) {
    $existe = false; // Ya existe
} else {
    $existe = true; // Disponible
}

if ($existe) {
    $sql_insert = "INSERT INTO COTI_CALENDARIO (ID_CLIENTE, ID_SOPORTE, ID_USUARIO, IDUSUARIO, FECHA_SOPORTE, HORA_INICIO, HORA_FIN, ESTADO_SOPORTE, COMENTARIO, ESTADO) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, 'Pendiente', ?,'A')";
    
    $stmt_insert = $conexion->prepare($sql_insert);
    if (!$stmt_insert) {
        die("Error al preparar la inserción: " . $conexion->error);
    }
    
    $stmt_insert->bind_param("ssssssss", 
        $idCliente, 
        $idSoporte, 
        $idUsuario, 
        $_SESSION['iduser'], 
        $fechaSoporte, 
        $timeIni, 
        $timeFin, 
        $comentario
    );
    
    if ($stmt_insert->execute()) {
        $correo_paciente = '';
        $sql_correo = "SELECT EMAIL FROM AG_PACIENTE WHERE IDPACIENTE = ?";
        $stmt_correo = $conexion->prepare($sql_correo);
        
        $stmt_correo->bind_param("s", $idCliente);
        $stmt_correo->execute();
        $result_correo = $stmt_correo->get_result();
        
        if ($result_correo && $result_correo->num_rows > 0) {
            $fila = $result_correo->fetch_assoc();
            $correo_paciente = $fila['EMAIL'];
        } else {
            $correo_paciente = 'jvaras@overclocking.com.ec'; 
        }

        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        try {
            $mail->isSMTP();
            $mail->Host = 'mail.overclocking.com.ec';
            $mail->SMTPAuth = true;
            $mail->Username = 'soporte@overclocking.com.ec';
            $mail->Password = 'xco3B;NUFj=T';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            $mail->setFrom('soporte@overclocking.com.ec', 'Sistema de reportes OVERCLOCKING');
            $mail->addAddress($correo_paciente);
            $mail->addCC('janviervaras@hotmail.com');
            $mail->addCC('belensarmiento@inasar.ec');
            $mail->addCC('msarmiento@inasar.ec');
            $mail->addCC('soporte@inasar.ec');

            $mail->Subject = 'Soporte Técnico Realizado - Comprobante de Servicio';
            
            // Usamos las nuevas variables $tipoSoporteTexto y $nombreTecnico
            $mail->Body = "Estimado/a cliente,\n\n"
            . "Le informamos que el servicio de soporte técnico programado ha sido completado exitosamente.\n\n"
            . "**Detalles del Servicio:**\n"
            . "- Fecha: $fechaSoporte\n"
            . "- Hora de inicio: $timeIni\n"
            . "- Hora de finalización: $timeFin\n"
            . "- Tipo de soporte: $tipoSoporteTexto\n" // <--- Cambio aquí
            . "- Técnico asignado: $nombreTecnico\n\n"    // <--- Cambio aquí
            . "**Resumen de Actividades Realizadas:**\n"
            . "$comentario\n\n"
            . "**Información Adicional:**\n"
            . "• Este comprobante sirve como constancia del servicio prestado.\n"
            . "• Para cualquier consulta o seguimiento, puede referirse al técnico $nombreTecnico.\n\n"
            . "Agradecemos su confianza en nuestros servicios.\n\n"
            . "Atentamente,\n"
            . "Equipo de Soporte Técnico";

            $mail->send();
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $e->getMessage());
        }

        echo "<script>
                window.location.href = '../SCH_Calendar_SOP.php';
              </script>";
    } else {
        echo "Error al crear la cita: " . $stmt_insert->error;
    }
} else {
    echo "<script>
            alert('¡La cita ya existe en este horario!');
            window.location.href = '../Home.php';
          </script>";
}

if (isset($stmt_valida)) $stmt_valida->close();
if (isset($stmt_insert)) $stmt_insert->close();
if (isset($stmt_correo)) $stmt_correo->close();
$conexion->close();
?>