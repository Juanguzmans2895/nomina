<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$filePath = 'C:\\Users\\jguzm\\Downloads\\LIQUIDACION DE NOMINA ENERO 2026.xlsx';

try {
    $reader = new Xlsx();
    $reader->setReadDataOnly(false); // Leer fórmulas
    $spreadsheet = $reader->load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    $colNum = columnLetterToNumber($highestColumn);
    
    // Leer encabezados
    $headers = [];
    for ($col = 1; $col <= $colNum; $col++) {
        $cell = $sheet->getCellByColumnAndRow($col, 1);
        $value = trim((string)($cell->getValue() ?? ''));
        if ($value === '') break;
        $headers[] = $value;
    }
    
    // Contar filas de datos
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
    
    // Verificar si última fila es total
    $lastRowContent = '';
    for ($col = 1; $col <= min(count($headers), 10); $col++) {
        $cell = $sheet->getCellByColumnAndRow($col, $lastDataRow);
        $lastRowContent .= ' ' . ($cell->getValue() ?? '');
    }
    $isLastRowTotal = preg_match('/TOTAL/i', $lastRowContent);
    if ($isLastRowTotal) $dataRowCount--;
    
    // Analizar tipos de datos
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
    
    // Extraer fórmulas
    $formulas = [];
    for ($col = 1; $col <= count($headers); $col++) {
        // Buscar la primera fórmula en esa columna (desde fila 2)
        for ($row = 2; $row <= min($lastDataRow, 50); $row++) {
            $cell = $sheet->getCellByColumnAndRow($col, $row);
            if ($cell->isFormula()) {
                $formula = (string)$cell->getValue();
                $headerName = $headers[$col - 1];
                $formulas[$headerName] = $formula;
                break;
            }
        }
    }
    
    // Crear resumen de fórmulas de cálculo común
    $calculationFormulas = [];
    if (!empty($formulas)) {
        // Agrupar por patrón de fórmula
        foreach ($formulas as $col => $formula) {
            // Detectar patrones comunes
            if (stripos($formula, 'SUM') !== false) {
                $pattern = 'SUM - Suma de rango';
            } elseif (stripos($formula, 'ROUND') !== false) {
                $pattern = 'ROUND - Redondeo de valores';
            } elseif (stripos($formula, 'IF') !== false) {
                $pattern = 'IF - Condicional';
            } elseif (stripos($formula, '*') !== false) {
                $pattern = 'Multiplicación simple';
            } elseif (stripos($formula, '/') !== false) {
                $pattern = 'División';
            } elseif (stripos($formula, '+') !== false) {
                $pattern = 'Suma';
            } else {
                $pattern = 'Referencia de celda';
            }
            
            if (!isset($calculationFormulas[$pattern])) {
                $calculationFormulas[$pattern] = [];
            }
            $calculationFormulas[$pattern][] = $col;
        }
    }
    
    // Construir resultado
    $output = [
        'columnas' => $headers,
        'total_filas_datos' => $dataRowCount,
        'tipos_datos' => $columnTypes,
        'secciones' => $sections,
        'formulas' => [
            'columnas_con_formulas' => array_keys($formulas),
            'patrones_detectados' => $calculationFormulas,
            'ejemplos_formulas' => array_slice($formulas, 0, 10),
            'total_columnas_con_formulas' => count($formulas)
        ],
        'metadata' => [
            'archivo' => basename($filePath),
            'ruta_completa' => $filePath,
            'total_columnas' => count($headers),
            'total_columnas_archivo' => $colNum,
            'total_filas_archivo' => $highestRow,
            'hoja' => trim($sheet->getTitle()),
            'ultima_columna_usado' => $highestColumn,
            'última_fila_es_total' => $isLastRowTotal
        ]
    ];
    
    // Guardar JSON a archivo
    $jsonFile = 'analisis_liquidacion_nomina.json';
    file_put_contents($jsonFile, json_encode($output, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    echo "JSON guardado en: $jsonFile\n\n";
    
    // Mostrar JSON
    echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
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
