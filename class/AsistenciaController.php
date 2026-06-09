<?php
class AsistenciaController {
    
    public function procesarExcel($archivo) {
        try {
            // Validar archivo
            if (!$this->validarArchivo($archivo)) {
                throw new Exception('Archivo no válido');
            }
            
            // Cargar PhpSpreadsheet
            $this->cargarPhpSpreadsheet();
            
            // Procesar con PhpSpreadsheet
            return $this->procesarConPhpSpreadsheet($archivo);
            
        } catch (Exception $e) {
            throw new Exception('Error en controlador: ' . $e->getMessage());
        }
    }
    
    private function cargarPhpSpreadsheet() {
        // Si ya está cargado, salir
        if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            return true;
        }
        
        // Rutas posibles para autoload
        $paths = [
            __DIR__ . '/../vendor/autoload.php', // Composer
            __DIR__ . '/../PhpSpreadsheet/src/autoload.php', // Manual
            __DIR__ . '/../../vendor/autoload.php', // Composer nivel superior
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return true;
            }
        }
        
        throw new Exception(
            'PhpSpreadsheet no encontrado. ' .
            'Instala via: composer require phpoffice/phpspreadsheet ' .
            'o descarga manualmente desde GitHub.'
        );
    }
    
    private function procesarConPhpSpreadsheet($archivo) {
        try {
            // Cargar el archivo Excel
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivo['tmp_name']);
            
            // LEER HOJA "Reporte Estadístico" - CAMBIO CLAVE
            $worksheet = $spreadsheet->getSheetByName('Reporte Estadístico');
            
            // Si no encuentra la hoja, intentar con índice 1 (segunda hoja)
            if (!$worksheet) {
                $worksheet = $spreadsheet->getSheet(1);
            }
            
            // Leer datos del Excel
            $datos = $this->leerDatosExcel($worksheet);
            
            // Procesar datos de asistencia
            return $this->procesarDatosAsistencia($datos);
            
        } catch (Exception $e) {
            throw new Exception('Error procesando Excel: ' . $e->getMessage());
        }
    }
    
    private function leerDatosExcel($worksheet) {
        $datos = [];
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        
        // Empezar desde la fila 4 (fila 1-3 son headers en "Reporte Estadístico")
        for ($row = 4; $row <= $highestRow; $row++) {
            $rowData = $worksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            
            // Si la fila está vacía, saltar
            if (empty(trim(implode('', $rowData[0])))) {
                continue;
            }
            
            // Si el ID está vacío, probablemente es una fila de header o separador
            if (empty($rowData[0][0]) || !is_numeric($rowData[0][0])) {
                continue;
            }
            
            // Mapear datos según estructura de "Reporte Estadístico"
            $empleado = $this->mapearFilaExcel($rowData[0], $row);
            if ($empleado) {
                $datos[] = $empleado;
            }
        }
        
        return $datos;
    }
    
    private function mapearFilaExcel($fila, $numeroFila) {
        // VALIDAR QUE TENGA DATOS BÁSICOS
        if (empty($fila[0]) || empty($fila[1])) {
            return null;
        }
        
        // MAPEO CORREGIDO para "Reporte Estadístico"
        return [
            'id' => $fila[0], // Columna A: ID
            'nombre' => $fila[1], // Columna B: Nombre
            'departamento' => $fila[2] ?? 'Empresa', // Columna C: Departamento
            'horas_normales' => $fila[3] ?? '0:00', // Columna D: Horas Laborales Normal
            'horas_reales' => $fila[4] ?? '0:00', // Columna E: Horas Laborales Real
            'retardos_cantidad' => $fila[5] ?? 0, // Columna F: Retardos Cantidad
            'retardos_minutos' => $fila[6] ?? 0, // Columna G: Retardos Minuto
            'salidas_cantidad' => $fila[7] ?? 0, // Columna H: Salidas Temprano Cantidad
            'salidas_minutos' => $fila[8] ?? 0, // Columna I: Salidas Temprano Minuto
            'dias_asistidos' => $fila[11] ?? '0/0', // Columna L: Días Asistidos (Normal/Real)
            'dias_falta' => $fila[13] ?? 0, // Columna N: Falta (Días)
            'total_horas_trabajadas' => $fila[4] ?? '0:00', // Usar horas reales como horas trabajadas
            'horas_faltantes' => $this->calcularHorasFaltantes($fila[3] ?? '0:00', $fila[4] ?? '0:00'),
            'estado' => $this->determinarEstadoReporteEstadistico($fila)
        ];
    }
    
    private function calcularHorasFaltantes($horasNormales, $horasReales) {
        $minutosNormales = $this->horasAMinutos($horasNormales);
        $minutosReales = $this->horasAMinutos($horasReales);
        
        $diferencia = $minutosNormales - $minutosReales;
        return $diferencia > 0 ? $this->minutosAHoras($diferencia) : '0:00';
    }
    
    private function determinarEstadoReporteEstadistico($fila) {
        $retardosMinutos = $fila[6] ?? 0;
        $diasFalta = $fila[13] ?? 0;
        $horasReales = $fila[4] ?? '0:00';
        
        // Convertir horas reales a minutos para evaluación
        $minutosReales = $this->horasAMinutos($horasReales);
        
        // Lógica de estado basada en el reporte estadístico
        if ($diasFalta > 10 || $minutosReales < 3000) { // Menos de 50 horas
            return '🔴 Asistencia Crítica';
        } elseif ($diasFalta > 5 || $retardosMinutos > 60 || $minutosReales < 6000) { // Menos de 100 horas
            return '🟡 Asistencia Regular';
        } else {
            return '✅ Asistencia Correcta';
        }
    }
    
    private function validarArchivo($archivo) {
        return $archivo && 
               isset($archivo['tmp_name']) && 
               file_exists($archivo['tmp_name']) &&
               $archivo['size'] > 0;
    }
    
    private function procesarDatosAsistencia($datos) {
        $totalHorasTrabajadas = 0;
        $totalHorasFaltantes = 0;
        $empleadosProblemas = 0;
        $empleadosBuenRendimiento = 0;
        
        foreach ($datos as $empleado) {
            // Convertir horas a minutos para cálculos
            $horasTrab = $this->horasAMinutos($empleado['total_horas_trabajadas']);
            $horasFalt = $this->horasAMinutos($empleado['horas_faltantes']);
            
            $totalHorasTrabajadas += $horasTrab;
            $totalHorasFaltantes += $horasFalt;
            
            // Contar empleados con problemas
            if (strpos($empleado['estado'], '🔴') !== false || 
                strpos($empleado['estado'], '🟡') !== false) {
                $empleadosProblemas++;
            } else {
                $empleadosBuenRendimiento++;
            }
        }
        
        return [
            'resumen' => [
                'total_empleados' => count($datos),
                'empleados_buen_rendimiento' => $empleadosBuenRendimiento,
                'empleados_problemas' => $empleadosProblemas,
                'total_horas_trabajadas' => $this->minutosAHoras($totalHorasTrabajadas),
                'total_horas_faltantes' => $this->minutosAHoras($totalHorasFaltantes)
            ],
            'detalle' => $datos,
            'alertas' => $this->generarAlertas($datos)
        ];
    }
    
    private function horasAMinutos($horasStr) {
        if (strpos($horasStr, ':') === false) return 0;
        list($horas, $minutos) = explode(':', $horasStr);
        return ($horas * 60) + $minutos;
    }
    
    private function minutosAHoras($minutos) {
        $horas = floor($minutos / 60);
        $minutosRest = $minutos % 60;
        return sprintf('%02d:%02d', $horas, $minutosRest);
    }
    
    private function generarAlertas($datos) {
        $alertas = [];
        
        foreach ($datos as $empleado) {
            if (strpos($empleado['estado'], '🔴') !== false) {
                $alertas[] = [
                    'tipo' => 'alto',
                    'mensaje' => $empleado['nombre'] . ' - Asistencia crítica',
                    'empleado_id' => $empleado['id']
                ];
            } elseif (strpos($empleado['estado'], '🟡') !== false) {
                $alertas[] = [
                    'tipo' => 'medio',
                    'mensaje' => $empleado['nombre'] . ' - Atención requerida',
                    'empleado_id' => $empleado['id']
                ];
            }
        }
        
        return $alertas;
    }
}
?>