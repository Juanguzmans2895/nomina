<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$filePath = 'C:\\Users\\jguzm\\Downloads\\LIQUIDACION DE NOMINA ENERO 2026.xlsx';

if (!file_exists($filePath)) {
    echo json_encode(['error' => "Archivo no encontrado: $filePath"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit(1);
}

try {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    
    $data = [
        'archivo' => $filePath,
        'columnas' => [],
        'tipos_datos' => [],
        'total_filas_datos' => 0,
        'secciones' => [],
        'formulas' => [],
        'estructura_detallada' => []
    ];
    
    // Obtener el rango de datos
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Coordinate\Coordinate::columnLetterToColumnIndex($highestColumn);
    
    // Extraer encabezados (primera fila)
    $headers = [];
    for ($col = 1; $col <= $highestColumnIndex; $col++) {
        $cell = $sheet->getCellByColumnAndRow($col, 1);
        $value = $cell->getValue();
        $headers[$col] = $value !== null ? trim((string)$value) : "Columna_$col";
    }
    
    $data['columnas'] = array_values($headers);
    
    // Analizar todas las filas para detectar estructura
    $rowsData = [];
    $dataSections = [];
    $lastNonEmptyRow = 0;
    $firstDataRow = 2; // Asumiendo que la fila 1 es encabezado
    
    for ($row = 2; $row <= $highestRow; $row++) {
        $rowData = [];
        $isEmptyRow = true;
        
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $cell = $sheet->getCellByColumnAndRow($col, $row);
            $value = $cell->getValue();
            
            if ($value !== null && $value !== '') {
                $isEmptyRow = false;
                $lastNonEmptyRow = $row;
            }
            
            $rowData[$col] = [
                'value' => $value,
                'type' => getPhpSpreadsheetType($cell),
                'formula' => $cell->isFormula() ? $cell->getValue() : null,
                'calculatedValue' => $cell->isFormula() ? $cell->getCalculatedValue() : null,
            ];
        }
        
        if (!$isEmptyRow) {
            $rowsData[$row] = $rowData;
        }
    }
    
    // Contar filas de datos (excluyendo posibles totales al final)
    $totalDataRows = count($rowsData);
    
    // Detectar si la última fila es un total
    if ($totalDataRows > 0) {
        $lastRowContent = array_values($rowsData);
        $lastRow = end($lastRowContent);
        
        // Revisar si contiene palabras clave de totales
        $lastRowText = implode(' ', array_map(fn($c) => (string)$c['value'], $lastRow));
        if (stripos($lastRowText, 'TOTAL') !== false || stripos($lastRowText, 'TOTALES') !== false) {
            $totalDataRows--; // Restar la fila de totales
        }
    }
    
    $data['total_filas_datos'] = $totalDataRows;
    
    // Detectar tipos de datos por columna
    foreach ($headers as $col => $header) {
        $types = [];
        foreach ($rowsData as $row) {
            if (isset($row[$col]['type'])) {
                $types[] = $row[$col]['type'];
            }
        }
        
        // Determinar tipo predominante
        $typeCounts = array_count_values($types);
        arsort($typeCounts);
        $data['tipos_datos'][$header] = key($typeCounts) ?? 'mixed';
    }
    
    // Detectar secciones y fórmulas
    $currentSection = null;
    $sectionColumns = [];
    
    foreach ($headers as $col => $header) {
        // Detectar secciones por palabras clave
        if (stripos($header, 'DEVENG') !== false) {
            $currentSection = 'DEVENGOS';
        } elseif (stripos($header, 'DEDUCC') !== false) {
            $currentSection = 'DEDUCCIONES';
        } elseif (stripos($header, 'APORT') !== false) {
            $currentSection = 'APORTES';
        } elseif (stripos($header, 'PROVISION') !== false) {
            $currentSection = 'PROVISIONES';
        } elseif (stripos($header, 'TOTAL') !== false || stripos($header, 'NETO') !== false) {
            $currentSection = 'TOTALES';
        }
        
        if ($currentSection) {
            if (!isset($sectionColumns[$currentSection])) {
                $sectionColumns[$currentSection] = [];
            }
            $sectionColumns[$currentSection][] = $header;
        }
    }
    
    $data['secciones'] = $sectionColumns;
    
    // Detectar fórmulas en la segunda fila (primera fila de datos)
    if (isset($rowsData[2])) {
        $formulasFound = [];
        foreach ($headers as $col => $header) {
            if (isset($rowsData[2][$col]['formula']) && $rowsData[2][$col]['formula']) {
                $formulasFound[$header] = $rowsData[2][$col]['formula'];
            }
        }
        $data['formulas'] = $formulasFound;
    }
    
    // Estructura detallada de las primeras 5 filas
    $rowCount = 0;
    foreach ($rowsData as $rowNum => $row) {
        if ($rowCount >= 5) break;
        
        $detailedRow = [];
        foreach ($headers as $col => $header) {
            if (isset($row[$col])) {
                $detailedRow[$header] = [
                    'value' => $row[$col]['value'],
                    'type' => $row[$col]['type'],
                    'is_formula' => $row[$col]['formula'] !== null,
                    'formula' => $row[$col]['formula']
                ];
            }
        }
        $data['estructura_detallada'][] = $detailedRow;
        $rowCount++;
    }
    
    // Información adicional
    $data['info_archivo'] = [
        'total_filas_en_hoja' => $highestRow,
        'ultima_columna' => $highestColumn,
        'numero_columnas' => $highestColumnIndex,
        'hoja_activa' => $sheet->getTitle()
    ];
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit(1);
}

function getPhpSpreadsheetType($cell) {
    $dataType = $cell->getDataType();
    
    switch ($dataType) {
        case 'b': return 'boolean';
        case 'd': return 'date';
        case 'e': return 'error';
        case 'n': return 'number';
        case 's': return 'string';
        case 'f': return 'formula';
        case 'inlineString': return 'string';
        default: return 'mixed';
    }
}
