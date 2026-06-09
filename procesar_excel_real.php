<?php
// procesar_excel_real.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();
session_start();
require_once("class/funciones.php");
require_once("class/conexionBD.php");
$conexion = conectarse();

header('Content-Type: application/json');

// Verificación de sesión
if(!isset($_SESSION["rol"])){
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Configuración
ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M');

try {
    // Verificar POST y archivo
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error en la subida del archivo');
    }

    $archivo = $_FILES['excel_file'];

    // Validaciones
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ['xls', 'xlsx'])) {
        throw new Exception('Formato no permitido. Use .xls o .xlsx');
    }

    // INCLUIR TODOS LOS ARCHIVOS NECESARIOS DE PhpSpreadsheet
    require_once __DIR__ . '/PhpSpreadsheet/src/PhpSpreadsheet/Spreadsheet.php';
    require_once __DIR__ . '/PhpSpreadsheet/src/PhpSpreadsheet/IOFactory.php';
    require_once __DIR__ . '/PhpSpreadsheet/src/PhpSpreadsheet/Reader/IReader.php';
    require_once __DIR__ . '/PhpSpreadsheet/src/PhpSpreadsheet/Reader/BaseReader.php';
    
    // Incluir el reader específico según la extensión
    if ($extension === 'xlsx') {
        require_once __DIR__ . '/PhpSpreadsheet/src/PhpSpreadsheet/Reader/Xlsx.php';
    } else {
        require_once __DIR__ . '/PhpSpreadsheet/src/PhpSpreadsheet/Reader/Xls.php';
    }
    
    // Crear el reader manualmente para evitar problemas con autoload
    if ($extension === 'xlsx') {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    } else {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
    }
    
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($archivo['tmp_name']);
    $worksheet = $spreadsheet->getActiveSheet();
    
    $empleados = [];
    $alertas = [];
    
    // DEFINIR LOS ÍNDICES DE COLUMNAS BASADO EN TU EXCEL
    $columnas = [
        'id' => 0,              // A - ID
        'nombre' => 1,          // B - Nombre
        'departamento' => 2,    // C - Departamento
        'horas_laborales' => 3, // D - Horas Laborales (Normal)
        'horas_reales' => 4,    // E - Horas Laborales (Real)
        'retardos_cantidad' => 5, // F - Retardos Cantidad
        'retardos_minutos' => 6, // G - Retardos Minuto
        'salidas_cantidad' => 7, // H - Salidas Temprano Cantidad
        'salidas_minutos' => 8, // I - Salidas Temprano Minuto
        'dias_asistidos_normal' => 13, // N - Días Asistidos (Normal)
        'dias_asistidos_real' => 14,  // O - Días Asistidos (Real)
        'dias_falta' => 16      // Q - Falta (Días)
    ];
    
    // PROCESAR FILAS (empezando desde fila 5, según tu formato)
    $fila_numero = 0;
    foreach ($worksheet->getRowIterator() as $row) {
        $fila_numero++;
        
        // Saltar las primeras 4 filas (encabezados)
        if ($fila_numero <= 4) {
            continue;
        }
        
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        
        $fila = [];
        foreach ($cellIterator as $cell) {
            $fila[] = $cell->getCalculatedValue();
        }
        
        // Saltar filas vacías o sin ID
        if (empty($fila[$columnas['id']]) || !is_numeric($fila[$columnas['id']])) {
            continue;
        }
        
        // PROCESAR DATOS DEL EMPLEADO
        $empleado = [
            'id' => intval($fila[$columnas['id']]),
            'nombre' => trim($fila[$columnas['nombre']] ?? ''),
            'departamento' => trim($fila[$columnas['departamento']] ?? ''),
            'horas_laborales' => formatearHora($fila[$columnas['horas_laborales']] ?? '0:00'),
            'horas_reales' => formatearHora($fila[$columnas['horas_reales']] ?? '0:00'),
            'retardos_cantidad' => intval($fila[$columnas['retardos_cantidad']] ?? 0),
            'retardos_minutos' => intval($fila[$columnas['retardos_minutos']] ?? 0),
            'salidas_cantidad' => intval($fila[$columnas['salidas_cantidad']] ?? 0),
            'salidas_minutos' => intval($fila[$columnas['salidas_minutos']] ?? 0),
            'dias_asistidos_normal' => intval($fila[$columnas['dias_asistidos_normal']] ?? 0),
            'dias_asistidos_real' => intval($fila[$columnas['dias_asistidos_real']] ?? 0),
            'dias_falta' => intval($fila[$columnas['dias_falta']] ?? 0)
        ];
        
        // CALCULAR HORAS FALTANTES
        $horas_esperadas = convertirHorasAMinutos($empleado['horas_laborales']);
        $horas_reales = convertirHorasAMinutos($empleado['horas_reales']);
        $horas_faltantes = max(0, $horas_esperadas - $horas_reales);
        
        $empleado['horas_faltantes'] = convertirMinutosAHoras($horas_faltantes);
        $empleado['total_horas_trabajadas'] = $empleado['horas_reales'];
        $empleado['dias_laborados'] = $empleado['dias_asistidos_real'];
        $empleado['retrasos_minutos'] = $empleado['retardos_minutos'];
        
        // CALCULAR ESTADO SEGÚN TUS CRITERIOS
        $empleado = calcularEstadoEmpleado($empleado);
        
        $empleados[] = $empleado;
        
        // GENERAR ALERTAS SI CORRESPONDE
        if ($empleado['estado_tipo'] === 'problema') {
            $alertas[] = [
                'tipo' => 'medio',
                'mensaje' => $empleado['nombre'] . ' - Atención requerida',
                'empleado_id' => $empleado['id']
            ];
        }
    }
    
    // GENERAR RESUMEN
    $resumen = generarResumen($empleados);
    
    $resultado = [
        'success' => true,
        'data' => [
            'resumen' => $resumen,
            'detalle' => $empleados,
            'alertas' => $alertas
        ],
        'mensaje' => 'Archivo procesado correctamente - ' . count($empleados) . ' empleados procesados',
        'archivo' => $archivo['name']
    ];

    echo json_encode($resultado);

} catch (Exception $e) {
    error_log("Error en procesar_excel_real.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'mensaje' => 'Error al procesar el archivo: ' . $e->getMessage()
    ]);
}

// FUNCIÓN PARA CALCULAR ESTADO DEL EMPLEADO SEGÚN TUS CRITERIOS
function calcularEstadoEmpleado($empleado) {
    $horas_faltantes_minutos = convertirHorasAMinutos($empleado['horas_faltantes']);
    $retrasos_minutos = $empleado['retardos_minutos'];
    $dias_falta = $empleado['dias_falta'];
    
    // CRITERIOS DE BUEN RENDIMIENTO:
    // 1. Cumplir horas (máximo 60 minutos faltantes = 1 hora)
    // 2. Llegar puntual (máximo 15 minutos de retraso total)
    // 3. No tener faltas injustificadas
    $cumple_horas = $horas_faltantes_minutos <= 60; // Máximo 1 hora faltante
    $es_puntual = $retrasos_minutos <= 15; // Máximo 15 minutos de retraso
    $sin_faltas = $dias_falta == 0; // Sin faltas
    
    if ($cumple_horas && $es_puntual && $sin_faltas) {
        $empleado['estado'] = '✅ Asistencia Correcta';
        $empleado['estado_tipo'] = 'bueno';
    } elseif ($horas_faltantes_minutos <= 180 && $retrasos_minutos <= 60 && $dias_falta <= 2) {
        $empleado['estado'] = '🟡 Asistencia Regular';
        $empleado['estado_tipo'] = 'medio';
    } else {
        $empleado['estado'] = '❌ Asistencia Problemática';
        $empleado['estado_tipo'] = 'problema';
    }
    
    return $empleado;
}

// FUNCIÓN PARA GENERAR RESUMEN
function generarResumen($empleados) {
    $total_empleados = count($empleados);
    $empleados_buenos = 0;
    $empleados_problema = 0;
    $total_horas_trabajadas = 0;
    $total_horas_faltantes = 0;
    
    foreach ($empleados as $empleado) {
        if ($empleado['estado_tipo'] === 'bueno') {
            $empleados_buenos++;
        } elseif ($empleado['estado_tipo'] === 'problema') {
            $empleados_problema++;
        }
        
        $total_horas_trabajadas += convertirHorasAMinutos($empleado['horas_reales']);
        $total_horas_faltantes += convertirHorasAMinutos($empleado['horas_faltantes']);
    }
    
    return [
        'total_empleados' => $total_empleados,
        'empleados_buen_rendimiento' => $empleados_buenos,
        'empleados_problemas' => $empleados_problema,
        'total_horas_trabajadas' => convertirMinutosAHoras($total_horas_trabajadas),
        'total_horas_faltantes' => convertirMinutosAHoras($total_horas_faltantes)
    ];
}

// FUNCIONES AUXILIARES
function formatearHora($valor) {
    if (is_numeric($valor)) {
        // Si es un número (como 145.0), convertirlo a formato horas:minutos
        $horas = floor($valor);
        $minutos = round(($valor - $horas) * 60);
        return sprintf('%02d:%02d', $horas, $minutos);
    }
    return $valor;
}

function convertirHorasAMinutos($hora_str) {
    if (empty($hora_str) || $hora_str == '0:00') return 0;
    
    if (is_numeric($hora_str)) {
        return intval($hora_str) * 60;
    }
    
    if (strpos($hora_str, ':') === false) return intval($hora_str) * 60;
    
    list($horas, $minutos) = explode(':', $hora_str);
    return intval($horas) * 60 + intval($minutos);
}

function convertirMinutosAHoras($minutos) {
    $horas = floor($minutos / 60);
    $minutos_restantes = $minutos % 60;
    return sprintf('%02d:%02d', $horas, $minutos_restantes);
}
?>