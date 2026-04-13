<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'C:\\Users\\jguzm\\Downloads\\LIQUIDACION DE NOMINA ENERO 2026.xlsx';

try {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    $highestColumnIndex = columnToIndex($highestColumn);
    
    // 1. Extraer TODAS las columnas
    $headers = [];
    for ($col = 1; $col <= $highestColumnIndex; $col++) {
        $cell = $sheet->getCellByColumnAndRow($col, 1);
        $value = $cell->getValue();
        $headers[$col] = $value !== null ? trim((string)$value) : "Columna_$col";
    }
    
    // 2. Leer todas las filas de datos
    $rowsData = [];
    $formulasUsed = [];
    
    for ($row = 2; $row <= $highestRow; $row++) {
        $rowData = [];
        $hasContent = false;
        
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $cell = $sheet->getCellByColumnAndRow($col, $row);
            $value = $cell->getValue();
            
            if ($value !== null && trim((string)$value) !== '') {
                $hasContent = true;
            }
            
            $rowData[$col] = [
                'value' => $value,
                'type' => getCellType($cell),
                'formula' => $cell->isFormula() ? (string)$cell->getValue() : null,
            ];
            
            // Guardar fórmulas para análisis
            if ($cell->isFormula()) {
                $colName = $headers[$col];
                $formula = (string)$cell->getValue();
                if (!isset($formulasUsed[$colName])) {
                    $formulasUsed[$colName] = [];
                }
                $formulasUsed[$colName][] = $formula;
            }
        }
        
        if ($hasContent) {
            $rowsData[$row] = $rowData;
        }
    }
    
    // 3. Contar filas de datos (excluir encabezados y totales)
    $totalDataRows = count($rowsData);
    $lastRowNum = end(array_keys($rowsData));
    
    // Verificar si la última fila es de totales
    $isTotalRow = false;
    if ($lastRowNum && isset($rowsData[$lastRowNum])) {
        $lastRowContent = array_map(fn($c) => (string)$c['value'] ?? '', $rowsData[$lastRowNum]);
        $lastRowText = implode(' ', $lastRowContent);
        if (preg_match('/TOTAL/i', $lastRowText)) {
            $isTotalRow = true;
            $totalDataRows--;
        }
    }
    
    // 4. Determinar tipos de datos por columna
    $columnTypes = [];
    foreach ($headers as $col => $header) {
        $types = [];
        foreach ($rowsData as $row) {
            if (isset($row[$col]['type'])) {
                $types[] = $row[$col]['type'];
            }
        }
        
        // Contar tipos
        $typeCounts = array_count_values($types);
        arsort($typeCounts);
        
        // Tipo predominante
        $predominantType = key($typeCounts) ?? 'mixed';
        $columnTypes[$header] = $predominantType;
    }
    
    // 5. Detectar secciones
    $secciones = [];
    $seccionActual = 'INFORMACIÓN BÁSICA';
    $columnasSeccion = [];
    
    foreach ($headers as $col => $header) {
        $headerUpper = strtoupper(trim($header));
        
        // Detectar cambios de sección
        if (preg_match('/DEVENG/i', $headerUpper)) {
            if ($seccionActual !== 'DEVENGOS') {
                if (!empty($columnasSeccion)) {
                    $secciones[$seccionActual] = $columnasSeccion;
                }
                $seccionActual = 'DEVENGOS';
                $columnasSeccion = [];
            }
        } elseif (preg_match('/DEDUCC/i', $headerUpper)) {
            if ($seccionActual !== 'DEDUCCIONES') {
                if (!empty($columnasSeccion)) {
                    $secciones[$seccionActual] = $columnasSeccion;
                }
                $seccionActual = 'DEDUCCIONES';
                $columnasSeccion = [];
            }
        } elseif (preg_match('/APORT/i', $headerUpper)) {
            if ($seccionActual !== 'APORTES') {
                if (!empty($columnasSeccion)) {
                    $secciones[$seccionActual] = $columnasSeccion;
                }
                $seccionActual = 'APORTES';
                $columnasSeccion = [];
            }
        } elseif (preg_match('/PROVISION/i', $headerUpper)) {
            if ($seccionActual !== 'PROVISIONES') {
                if (!empty($columnasSeccion)) {
                    $secciones[$seccionActual] = $columnasSeccion;
                }
                $seccionActual = 'PROVISIONES';
                $columnasSeccion = [];
            }
        } elseif (preg_match('/TOTAL|NETO/i', $headerUpper)) {
            if ($seccionActual !== 'TOTALES') {
                if (!empty($columnasSeccion)) {
                    $secciones[$seccionActual] = $columnasSeccion;
                }
                $seccionActual = 'TOTALES';
                $columnasSeccion = [];
            }
        }
        
        $columnasSeccion[] = $header;
    }
    
    // Agregar última sección
    if (!empty($columnasSeccion)) {
        $secciones[$seccionActual] = $columnasSeccion;
    }
    
    // 6. Extraer fórmulas únicas
    $formulasUnicas = [];
    foreach ($formulasUsed as $columnName => $formulas) {
        // Tomar la primera fórmula única de cada columna
        $formulasUnicas[$columnName] = array_values(array_unique($formulas))[0];
    }
    
    // 7. Construir respuesta JSON
    $resultado = [
        'columnas' => array_values($headers),
        'total_filas_datos' => $totalDataRows,
        'total_filas_archivo' => $highestRow,
        'tiene_fila_totales' => $isTotalRow,
        'tipos_datos' => $columnTypes,
        'secciones' => $secciones,
        'formulas' => $formulasUnicas,
        'numero_total_columnas' => $highestColumnIndex,
        'ultima_columna' => $highestColumn,
        'hoja' => $sheet->getTitle(),
        'resumen' => [
            'empleados' => $totalDataRows,
            'periodo' => 'ENERO 2026',
            'columnas_info_basica' => array_slice(array_values($headers), 0, 7),
            'columnas_devengos' => $secciones['DEVENGOS'] ?? [],
            'columnas_deducciones' => array_slice($secciones['DEDUCCIONES'] ?? [], 0, 10),
            'columnas_aportes' => $secciones['APORTES'] ?? [],
            'columnas_provisiones' => $secciones['PROVISIONES'] ?? [],
        ]
    ];
    
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit(1);
}

function getCellType($cell) {
    $dataType = $cell->getDataType();
    
    // Si es formula, también verificar el tipo de resultado
    if ($dataType === 'f') {
        return 'formula';
    }
    
    switch ($dataType) {
        case 'b': return 'boolean';
        case 'd': return 'date';
        case 'e': return 'error';
        case 'n': return 'number';
        case 's': 
        case 'inlineString': 
        case 'str': 
            return 'string';
        default: return 'mixed';
    }
}

function columnToIndex($column) {
    $index = 0;
    $multiplier = 1;
    
    for ($i = strlen($column) - 1; $i >= 0; $i--) {
        $index += (ord(strtoupper($column[$i])) - 64) * $multiplier;
        $multiplier *= 26;
    }
    
    return $index;
}
