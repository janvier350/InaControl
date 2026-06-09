<?php
// class/ProcesadorAsistencia.php
// Versión adaptada para usar la matriz de datos de SimpleXLSX

class ProcesadorAsistencia
{
    private $dataRows;
    private $fechas = [];
    private $datosProcesados = [];
    // Las marcaciones están en la Fila N+1.
    // El índice 0 es la primera fila de datos (ID: 1).
    private $DATA_START_ROW_INDEX = 4; // Fila 5 en Excel (ID: 4)

    public function __construct(array $dataRows)
    {
        $this->dataRows = $dataRows;
    }

    public function ejecutarProcesamiento(): array
    {
        // 1. Extraer Fechas (desde la Fila 4 de Excel, que es el índice 3 en el array)
        $this->extraerFechas($this->dataRows[3]);
        
        // 2. Iterar por bloques de 3 filas (Fila N, N+1, N+2)
        $this->iterarPorEmpleados();
        
        return $this->datosProcesados;
    }

    // Paso A: Extrae el rango de fechas de la Fila 4 de Excel (índice 3 del array)
    private function extraerFechas(array $headerRow): void
    {
        $this->fechas = [];
        // Itera sobre las celdas de la fila de encabezado
        foreach ($headerRow as $colIndex => $cellValue) {
            $value = trim((string)$cellValue);
            // El valor es el día (ej: 30, 1, 2, 3...)
            if (!empty($value) && is_numeric($value) && $value < 32) {
                // Se guarda el índice de columna y el valor (día)
                $this->fechas[$colIndex] = $value;
            }
        }
    }

    // Paso B: Itera sobre los bloques de 3 filas por empleado
    private function iterarPorEmpleados(): void
    {
        $periodoReporte = $this->extraerPeriodoBase(); 

        // Los datos de empleados comienzan en la Fila 5 (índice 4) y saltan de 3 en 3.
        for ($row = $this->DATA_START_ROW_INDEX; $row < count($this->dataRows); $row += 3) {
            
            $currentRow = $this->dataRows[$row]; // Fila N (Datos generales)
            $dataRow = $this->dataRows[$row + 1] ?? null; // Fila N+1 (Marcaciones)
            
            // Extraer Nombre (Columna I, que es el índice 8)
            $nombre = trim((string)($currentRow[8] ?? '')); 
            
            if (empty($nombre) || strpos($nombre, 'Nombre') !== false) {
                break; // Detener la iteración si ya no hay nombres válidos
            }

            // Procesar las marcaciones
            if ($dataRow) {
                $this->procesarMarcaciones($nombre, $periodoReporte, $dataRow);
            }
        }
    }

    // Paso C y D: Procesa todas las marcaciones de un empleado en un periodo
    private function procesarMarcaciones(string $nombre, string $periodoReporte, array $dataRow): void
    {
        foreach ($this->fechas as $colIndex => $dia) {
            
            $marcacionesConcatenadas = (string)($dataRow[$colIndex] ?? '');
            $marcacionesConcatenadas = trim($marcacionesConcatenadas);

            // Determinar la fecha completa para el cálculo
            $fechaCompleta = $periodoReporte . '-' . str_pad($dia, 2, '0', STR_PAD_LEFT);
            
            if (empty($marcacionesConcatenadas)) {
                $this->guardarResultado($nombre, $fechaCompleta, 'FALTA');
                continue;
            }

            // PARSEO DE MARCAS: Usa expresiones regulares para extraer los bloques HH:MM (ej: 06:55)
            $marcaciones = [];
            if (preg_match_all('/\d{2}:\d{2}/', $marcacionesConcatenadas, $matches)) {
                $marcaciones = $matches[0];
            } else {
                $this->guardarResultado($nombre, $fechaCompleta, 'ERROR DE FORMATO');
                continue;
            }

            // Paso E & F: Estructuración y Cálculo
            $this->calcularHoras($nombre, $fechaCompleta, $marcaciones);
        }
    }

    // Paso F: Cálculo y Validaciones (misma lógica que antes)
    private function calcularHoras(string $nombre, string $fecha, array $marcaciones): void
    {
        // ... (Dejo la lógica de calcularHoras y guardarResultado igual que antes) ...
        // Por brevedad en la respuesta, el código es el mismo que en la respuesta anterior.
        $count = count($marcaciones);

        if ($count < 4) {
             $this->guardarResultado($nombre, $fecha, 'INCOMPLETO ('.$count.' marcas)', $marcaciones);
             return;
        }

        $e1 = $marcaciones[0] ?? ''; 
        $s1 = $marcaciones[1] ?? ''; 
        $e2 = $marcaciones[2] ?? ''; 
        $s2 = $marcaciones[3] ?? ''; 

        try {
            // Usamos DateTime para manejar los cálculos de tiempo
            $dtE1 = new DateTime($fecha . ' ' . $e1);
            $dtS1 = new DateTime($fecha . ' ' . $s1);
            $dtE2 = new DateTime($fecha . ' ' . $e2);
            $dtS2 = new DateTime($fecha . ' ' . $s2);
            
            if ($dtS1 <= $dtE1 || $dtS2 <= $dtE2 || $dtE2 < $dtS1) {
                throw new Exception("Error Lógico de Tiempos.");
            }

            $diff1 = $dtE1->diff($dtS1);
            $diff2 = $dtE2->diff($dtS2);

            $totalSegundos = $diff1->h * 3600 + $diff1->i * 60;
            $totalSegundos += $diff2->h * 3600 + $diff2->i * 60;

            $horas = floor($totalSegundos / 3600);
            $minutos = floor(($totalSegundos % 3600) / 60);
            $totalHorasStr = str_pad($horas, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutos, 2, '0', STR_PAD_LEFT);

            $this->guardarResultado($nombre, $fecha, $totalHorasStr, [$e1, $s1, $e2, $s2]);

        } catch (Exception $e) {
            $this->guardarResultado($nombre, $fecha, 'ERROR CALCULO', [$e1, $s1, $e2, $s2]);
        }
    }

    // Extrae el año y mes del periodo (Ej: 2025-10)
    private function extraerPeriodoBase(): string
    {
        // La fila 3 de Excel es el índice 2 del array. La columna 3 es el índice 2.
        $periodoStr = $this->dataRows[2][2] ?? '';
        
        if (preg_match('/(\d{4}-\d{2})/', $periodoStr, $matches)) {
             return $matches[1];
        }
        
        return date('Y-m');
    }

    // Almacena el resultado en el arreglo final
    private function guardarResultado(string $nombre, string $fecha, string $totalHoras, array $marcaciones = []): void
    {
        $this->datosProcesados[] = [
            'nombre' => $nombre,
            'fecha' => $fecha,
            'entrada_1' => $marcaciones[0] ?? 'N/A',
            'salida_1' => $marcaciones[1] ?? 'N/A',
            'entrada_2' => $marcaciones[2] ?? 'N/A',
            'salida_2' => $marcaciones[3] ?? 'N/A',
            'total_horas' => $totalHoras,
        ];
    }
}