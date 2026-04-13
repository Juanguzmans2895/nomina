<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$filePath = 'C:\\Users\\jguzm\\Downloads\\LIQUIDACION DE NOMINA ENERO 2026.xlsx';

try {
    // Leer con filtros para no cargar todo en memoria
    $reader = new Xlsx();
    $reader->setReadDataOnly(true);
    
    echo "Cargando archivo...\n";
    $spreadsheet = $reader->load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    $colNum = columnLetterToNumber($highestColumn);
    
    echo "Archivo cargado: A1:$highestColumn$highestRow\n";
    echo "Columnas totales: $colNum, Filas totales: $highestRow\n\n";
    
    // Leer encabezados de la primera fila
    $headers = [];
    for ($col = 1; $col <= $colNum; $col++) {
        $cell = $sheet->getCellByColumnAndRow($col, 1);
        $value = trim((string)($cell->getValue() ?? ''));
        if ($value === '') {
            // Si encontramos una columna vacía, consideramos que ahí terminan los datos
            break;
        }
        $headers[] = $value;
    }
    
    echo "Encabezados extraídos: " . count($headers) . "\n\n";
    
    // Primeros 10 encabezados
    echo "Primeros 10 encabezados:\n";
    foreach (array_slice($headers, 0, 10) as $i => $h) {
        echo "  " . ($i + 1) . ". \"$h\"\n";
    }
    
    // Últimos 10 encabezados
    echo "\nÚltimos 10 encabezados:\n";
    foreach (array_slice($headers, -10) as $i => $h) {
        echo "  " . (count($headers) - 10 + $i + 1) . ". \"$h\"\n";
    }
    
    // Contar filas de datos reales
    $dataRowCount = 0;
    $lastDataRow = 1;
    for ($row = 2; $row <= $highestRow; $row++) {
        $hasData = false;
        for ($col = 1; $col <= min(count($headers), 5); $col++) {
            $cell = $sheet->getCellByColumnAndRow($col, $row);
            $value = $cell->getValue();
            if ($value !== null && trim((string)$value) !== '') {
                $hasData = true;
                break;
            }
        }
        if ($hasData) {
            $dataRowCount++;
            $lastDataRow = $row;
        }
    }
    
    echo "\nTotal de filas con datos: $dataRowCount\n";
    echo "Última fila con datos: $lastDataRow\n";
    
    // Verificar si la última fila es totales
    $lastRowContent = '';
    for ($col = 1; $col <= min(count($headers), 10); $col++) {
        $cell = $sheet->getCellByColumnAndRow($col, $lastDataRow);
        $lastRowContent .= ' ' . ($cell->getValue() ?? '');
    }
    $isLastRowTotal = preg_match('/TOTAL/i', $lastRowContent);
    echo "Última fila es total: " . ($isLastRowTotal ? "SÍ" : "NO") . "\n";
    
    if ($isLastRowTotal) {
        $dataRowCount--;
    }
    
    // Analizar tipos de datos (muestra de 5 filas)
    $columnTypes = [];
    for ($col = 1; $col <= count($headers); $col++) {
        $types = [];
        for ($row = 2; $row <= min($highestRow, 10); $row++) {
            $cell = $sheet->getCellByColumnAndRow($col, $row);
            $value = $cell->getValue();
            if ($value !== null && trim((string)$value) !== '') {
                $type = detectType($value);
                if ($type) $types[] = $type;
            }
        }
        if (!empty($types)) {
            $typeCounts = array_count_values($types);
            arsort($typeCounts);
            $columnTypes[$headers[$col - 1]] = array_key_first($typeCounts) ?? 'string';
        } else {
            $columnTypes[$headers[$col - 1]] = 'string';
        }
    }
    
    // Detectar secciones
    $sections = [];
    $currentSection = 'INFORMACIÓN BÁSICA';
    $sectionCols = [];
    
    foreach ($headers as $header) {
        $headerUpper = strtoupper($header);
        
        if (preg_match('/DEVENG|SALARIO\s+BASICO|AUXILIO|HORAS|REC\./', $headerUpper)) {
            if ($currentSection !== 'DEVENGOS') {
                if (!empty($sectionCols)) $sections[$currentSection] = $sectionCols;
                $currentSection = 'DEVENGOS';
                $sectionCols = [];
            }
        } elseif (preg_match('/DEDUCC|SALUD|PENSION|DESCUENTO|EMBARGO/', $headerUpper)) {
            if ($currentSection !== 'DEDUCCIONES') {
                if (!empty($sectionCols)) $sections[$currentSection] = $sectionCols;
                $currentSection = 'DEDUCCIONES';
                $sectionCols = [];
            }
        } elseif (preg_match('/APORT/', $headerUpper)) {
            if ($currentSection !== 'APORTES') {
                if (!empty($sectionCols)) $sections[$currentSection] = $sectionCols;
                $currentSection = 'APORTES';
                $sectionCols = [];
            }
        } elseif (preg_match('/PROVISION/', $headerUpper)) {
            if ($currentSection !== 'PROVISIONES') {
                if (!empty($sectionCols)) $sections[$currentSection] = $sectionCols;
                $currentSection = 'PROVISIONES';
                $sectionCols = [];
            }
        } elseif (preg_match('/TOTAL|NETO/', $headerUpper)) {
            if ($currentSection !== 'TOTALES') {
                if (!empty($sectionCols)) $sections[$currentSection] = $sectionCols;
                $currentSection = 'TOTALES';
                $sectionCols = [];
            }
        }
        
        $sectionCols[] = $header;
    }
    if (!empty($sectionCols)) $sections[$currentSection] = $sectionCols;
    
    // Construir resultado en JSON
    $output = [
        'columnas' => $headers,
        'total_filas_datos' => $dataRowCount,
        'tipos_datos' => $columnTypes,
        'secciones' => $sections,
        'formulas' => [
            'nota' => 'No se detectaron fórmulas visibles en el análisis inicial'
        ],
        'metadata' => [
            'archivo' => basename($filePath),
            'total_columnas' => count($headers),
            'total_filas_archivo' => $highestRow,
            'hoja' => $sheet->getTitle(),
            'ultima_columna' => $highestColumn
        ]
    ];
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "RESULTADO EN JSON:\n";
    echo str_repeat("=", 80) . "\n";
    echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

function columnLetterToNumber($letter) {
    $letter = strtoupper($letter);
    $num = 0;
    $multiplier = 1;
    for ($i = strlen($letter) - 1; $i >= 0; $i--) {
        $digit = ord($letter[$i]) - 64;
        $num += $digit * $multiplier;
        $multiplier *= 26;
    }
    return $num;
}

function detectType($value) {
    $str = trim((string)$value);
    
    if ($str === '') return null;
    if (strpos($str, '=') === 0) return 'formula';
    if (is_numeric($str)) return 'number';
    if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{2,4}/', $str)) return 'date';
    if (in_array(strtolower($str), ['true', 'false', 'si', 'no'])) return 'boolean';
    return 'string';
}
