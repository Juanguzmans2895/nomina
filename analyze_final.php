<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'C:\\Users\\jguzm\\Downloads\\LIQUIDACION DE NOMINA ENERO 2026.xlsx';

try {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    
    // Convertir columna letra a número
    $colNum = columnLetterToNumber($highestColumn);
    echo "Rango de datos: A1 a $highestColumn$highestRow ($colNum columnas x $highestRow filas)\n";
    
    // Leer toda la hoja como array
    $range = "A1:$highestColumn$highestRow";
    $data = $sheet->rangeToArray($range, null, true, true, true);
    
    // Los encabezados son la primera fila
    $headers = $data[1];
    $headers = array_map(function($h) { return trim((string)($h ?? '')); }, $headers);
    $headers = array_values($headers); // Re-indexar
    
    echo "\nEncabezados totales: " . count($headers) . "\n";
    
    // Contar filas con datos (excluyendo encabezados)
    $dataRows = 0;
    $lastRowWithData = 1;
    
    for ($row = 2; $row <= $highestRow; $row++) {
        if (isset($data[$row])) {
            $rowContent = array_filter($data[$row], function($cell) {
                return $cell !== null && trim((string)$cell) !== '';
            });
            if (!empty($rowContent)) {
                $dataRows++;
                $lastRowWithData = $row;
            }
        }
    }
    
    echo "Total de filas con datos: $dataRows\n";
    echo "Última fila con datos: $lastRowWithData\n";
    
    // Detectar si la última fila es totales
    $lastRowIsTotal = false;
    if (isset($data[$lastRowWithData])) {
        $lastRowStr = implode(' ', array_map(fn($c) => (string)($c ?? ''), $data[$lastRowWithData]));
        if (preg_match('/TOTAL/i', $lastRowStr)) {
            $lastRowIsTotal = true;
            $dataRows--;
        }
    }
    
    // Detectar tipos de datos
    $columnTypes = [];
    foreach ($headers as $colIdx => $header) {
        $types = [];
        for ($row = 2; $row <= $lastRowWithData && $row - 1 <= $dataRows + 1; $row++) {
            if (isset($data[$row][$colIdx + 1])) {
                $cellValue = $data[$row][$colIdx + 1];
                if ($cellValue !== null && trim((string)$cellValue) !== '') {
                    $type = getValueType($cellValue);
                    $types[] = $type;
                }
            }
        }
        
        $typeCounts = array_count_values($types);
        arsort($typeCounts);
        $columnTypes[trim((string)$header)] = key($typeCounts) ?? 'string';
    }
    
    // Detectar secciones
    $sections = [];
    $currentSection = 'INFORMACIÓN BÁSICA';
    $sectionColumns = [];
    
    foreach ($headers as $header) {
        $headerTrimmed = trim((string)$header);
        if ($headerTrimmed === '') continue;
        
        $headerUpper = strtoupper($headerTrimmed);
        
        // Detectar cambios de sección
        if (preg_match('/DEVENG|SALARIO BASICO|AUXILIO|HORAS|DEVENGO/i', $headerUpper)) {
            if ($currentSection !== 'DEVENGOS') {
                if (!empty($sectionColumns)) {
                    $sections[$currentSection] = $sectionColumns;
                }
                $currentSection = 'DEVENGOS';
                $sectionColumns = [];
            }
        } elseif (preg_match('/DEDUCC|SALUD|PENSION|DESCUENTO|EMBARGO/i', $headerUpper)) {
            if ($currentSection !== 'DEDUCCIONES') {
                if (!empty($sectionColumns)) {
                    $sections[$currentSection] = $sectionColumns;
                }
                $currentSection = 'DEDUCCIONES';
                $sectionColumns = [];
            }
        } elseif (preg_match('/APORT/i', $headerUpper)) {
            if ($currentSection !== 'APORTES') {
                if (!empty($sectionColumns)) {
                    $sections[$currentSection] = $sectionColumns;
                }
                $currentSection = 'APORTES';
                $sectionColumns = [];
            }
        } elseif (preg_match('/PROVISION/i', $headerUpper)) {
            if ($currentSection !== 'PROVISIONES') {
                if (!empty($sectionColumns)) {
                    $sections[$currentSection] = $sectionColumns;
                }
                $currentSection = 'PROVISIONES';
                $sectionColumns = [];
            }
        } elseif (preg_match('/TOTAL|NETO/i', $headerUpper)) {
            if ($currentSection !== 'TOTALES') {
                if (!empty($sectionColumns)) {
                    $sections[$currentSection] = $sectionColumns;
                }
                $currentSection = 'TOTALES';
                $sectionColumns = [];
            }
        }
        
        $sectionColumns[] = $headerTrimmed;
    }
    
    if (!empty($sectionColumns)) {
        $sections[$currentSection] = $sectionColumns;
    }
    
    // Buscar fórm ulas usando regex en los strings
    $formulas = [];
    
    // Mostrar resultado
    $resultado = [
        'columnas' => $headers,
        'total_filas_datos' => $dataRows,
        'tipos_datos' => $columnTypes,
        'secciones' => $sections,
        'numero_columnas' => count($headers),
        'info' => [
            'archivo' => basename($filePath),
            'ruta_archivo' => $filePath,
            'hoja' => $sheet->getTitle(),
            'ultima_fila_es_total' => $lastRowIsTotal,
            'rango_datos' => "A1:$highestColumn$highestRow"
        ]
    ];
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

function columnLetterToNumber($letter) {
    $letter = strtoupper($letter);
    $num = 0;
    $multipler = 1;
    for ($i = strlen($letter) - 1; $i >= 0; $i--) {
        $digit = ord($letter[$i]) - 64;
        $num += $digit * $multipler;
        $multipler *= 26;
    }
    return $num;
}

function getValueType($value) {
    $value = trim((string)$value);
    
    // Verificar si es fórmula
    if (strpos($value, '=') === 0) {
        return 'formula';
    }
    
    // Verificar si es fecha
    if (preg_match('/^\d{4}-\d{2}-\d{2}|\d{2}\/\d{2}\/\d{4}|\d{2}-\d{2}-\d{4}/', $value)) {
        return 'date';
    }
    
    // Verificar si es número
    if (is_numeric($value)) {
        return 'number';
    }
    
    // Verificar si es booleano
    if (in_array(strtolower($value), ['true', 'false', 'si', 'no'])) {
        return 'boolean';
    }
    
    return 'string';
}
