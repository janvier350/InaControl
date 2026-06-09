<?php
// /home/overcloc/public_html/inaControl/procesar_excel.php
ob_start();
session_start();
require_once("class/funciones.php");
require_once("class/conexionBD.php");
$conexion = conectarse();

header('Content-Type: application/json');

// Verificación de sesión y tiempo de expiración
if(!isset($_SESSION["rol"])){
    header("Location: break.php");
    exit;
} else {
    $now = time();
    if ($now > $_SESSION['expire']) {
        session_destroy();
        header("Location: expirada.php");
        exit;
    }
}

// Configuración de seguridad
ini_set('max_execution_time', 300); // 5 minutos para procesar
ini_set('memory_limit', '512M');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Crear directorio temporal
$uploadDir = __DIR__ . '/temp/';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode(['error' => 'No se pudo crear directorio temporal']);
        exit;
    }
}

try {
    // Verificar que sea una solicitud POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }
    
    file_put_contents($uploadDir . 'debug.log', "Método POST verificado\n", FILE_APPEND);

    // Verificar que se haya subido un archivo
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = 'Error en la subida del archivo: ';
        switch ($_FILES['excel_file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $errorMsg .= 'El archivo excede el tamaño permitido';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg .= 'El archivo excede el tamaño del formulario';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMsg .= 'El archivo se subió parcialmente';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg .= 'No se seleccionó ningún archivo';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errorMsg .= 'Falta carpeta temporal';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errorMsg .= 'Error al escribir en el disco';
                break;
            default:
                $errorMsg .= 'Error desconocido';
        }
        throw new Exception($errorMsg);
    }

    file_put_contents($uploadDir . 'debug.log', "Archivo recibido: " . $_FILES['excel_file']['name'] . "\n", FILE_APPEND);

    $archivo = $_FILES['excel_file'];

    // Validaciones de seguridad
    $extensionesPermitidas = ['xls', 'xlsx'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $extensionesPermitidas)) {
        throw new Exception('Formato de archivo no permitido. Use .xls o .xlsx');
    }

    if ($archivo['size'] > 10 * 1024 * 1024) { // 10MB máximo
        throw new Exception('El archivo es demasiado grande. Máximo 10MB');
    }

    // Generar nombre único para el archivo
    $nombreArchivo = uniqid() . '_' . time() . '.' . $extension;
    $rutaTemporal = $uploadDir . $nombreArchivo;

    // Mover archivo a carpeta temporal
    if (!move_uploaded_file($archivo['tmp_name'], $rutaTemporal)) {
        throw new Exception('Error al guardar el archivo en el servidor');
    }
    
    file_put_contents($uploadDir . 'debug.log', "Archivo movido exitosamente\n", FILE_APPEND);

    // Incluir y verificar el controlador
    $controllerPath = 'class/AsistenciaController.php';
    if (!file_exists($controllerPath)) {
        throw new Exception("Archivo del controlador no encontrado: " . $controllerPath);
    }
    
    require_once $controllerPath;
    
    if (!class_exists('AsistenciaController')) {
        throw new Exception("Clase AsistenciaController no encontrada");
    }

    // Procesar el archivo
    $controller = new AsistenciaController();
    $resultado = $controller->procesarExcel([
        'tmp_name' => $rutaTemporal,
        'name' => $archivo['name'],
        'size' => $archivo['size'],
        'type' => $archivo['type']
    ]);

    // Limpiar archivo temporal
    if (file_exists($rutaTemporal)) {
        unlink($rutaTemporal);
    }

    // Registrar en log del sistema
    error_log("InaControl - Archivo procesado: " . $archivo['name'] . " por usuario: " . $_SESSION['rol']);

    // Devolver resultado - ESTO DEBE IR AL FINAL
    echo json_encode([
        'success' => true,
        'data' => $resultado,
        'mensaje' => 'Archivo procesado correctamente - ' . count($resultado['detalle']) . ' empleados procesados'
    ]);

} catch (Exception $e) {
    // Limpiar en caso de error
    if (isset($rutaTemporal) && file_exists($rutaTemporal)) {
        unlink($rutaTemporal);
    }

    // Log del error
    error_log("InaControl - Error procesando Excel: " . $e->getMessage());

    // Respuesta de error
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'mensaje' => 'Error al procesar el archivo Excel'
    ]);
}

file_put_contents($uploadDir . 'debug.log', "Fin procesamiento: " . date('Y-m-d H:i:s') . "\n\n", FILE_APPEND);
?>