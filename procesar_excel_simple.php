<?php
// procesar_excel_simple.php (Versi車n SimpleXLSX sin Composer)

// ----------------------------------------------------------------------
// 1. Incluir las clases (SIN autoload.php)
// ----------------------------------------------------------------------

// Incluir la librer赤a SimpleXLSX (debe estar en class/)
//require __DIR__ . '/class/SimpleXLSX.php';
require 'class/SimpleXLSX.php';

// Incluir la clase de procesamiento desde la carpeta 'class'
require __DIR__ . '/class/ProcesadorAsistencia.php';


// Funci車n de utilidad para manejar y responder errores
header('Content-Type: application/json');
function sendError($message, $httpCode = 500) {
    http_response_code($httpCode);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

// ... (El resto del manejo de POST y subida de archivo es igual) ...

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['excel_file'])) {
    sendError('Acceso no permitido o archivo no enviado.', 400);
}

$file = $_FILES['excel_file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    sendError('Error al subir el archivo. C車digo de error: ' . $file['error']);
}

// ... (Validaciones de tipo de archivo) ...

$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
$uniqueName = uniqid('reporte_', true) . '.' . $fileExtension;
$targetDir = __DIR__ . '/temp/';
$targetFile = $targetDir . $uniqueName;

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
    sendError('No se pudo guardar el archivo en la carpeta temporal.');
}

// ----------------------------------------------------------------------
// 2. Procesamiento del Excel con SimpleXLSX
// ----------------------------------------------------------------------

try {
    // 1. Parsear el archivo. SimpleXLSX devuelve false si falla.
    if (!($xlsx = SimpleXLSX::parse($targetFile))) {
        throw new Exception("Error al abrir o leer el archivo Excel. Aseg迆rate de que no est芍 corrupto.");
    }
    
    // 2. Obtener la matriz de datos de la hoja "REPORTE DE ASISTENCIA"
    // El m谷todo rows() recibe el 赤ndice de la hoja (0 es la primera hoja).
    // Debemos encontrar el 赤ndice de la hoja por su nombre.
    
    $sheetIndex = array_search('REPORTE DE ASISTENCIA', $xlsx->sheetNames());

    if ($sheetIndex === false) {
        throw new Exception("No se encontr車 la hoja 'REPORTE DE ASISTENCIA'.");
    }
    
    // Obtener todas las filas de la hoja como una matriz (array)
    $dataRows = $xlsx->rows($sheetIndex);


    // 3. Inicializar y ejecutar la l車gica de procesamiento con la matriz de datos
    $procesador = new ProcesadorAsistencia($dataRows);
    $datosDetalle = $procesador->ejecutarProcesamiento();

    // ------------------------------------------------------------------
    // 4. Devolver Respuesta JSON
    // ------------------------------------------------------------------
    
    $response = [
        'success' => true,
        'mensaje' => 'Archivo procesado con 谷xito. Resultados listos.',
        'data' => [
            'detalle_asistencia' => $datosDetalle,
            'resumen' => [
                'total_empleados' => count(array_unique(array_column($datosDetalle, 'nombre'))),
                'total_registros' => count($datosDetalle),
                'errores_marcacion' => count(array_filter(array_column($datosDetalle, 'total_horas'), fn($h) => strpos($h, 'ERROR') !== false || $h === 'INCOMPLETO' || $h === 'FALTA')),
            ],
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    if (file_exists($targetFile)) {
        unlink($targetFile);
    }
    sendError('Error al procesar el archivo: ' . $e->getMessage());

} finally {
    if (file_exists($targetFile)) {
        // En producci車n, es recomendable borrar el archivo temporal
        // unlink($targetFile); 
    }
}
?>