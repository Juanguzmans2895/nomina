<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'C:\\Users\\jguzm\\Downloads\\LIQUIDACION DE NOMINA ENERO 2026.xlsx';

try {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    
    // Obtener coordenadas del rango de datos
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    
    echo "Procesando archivo...\n";
    echo "Filas: $highestRow, Columnas: $highestColumn\n\n";
    
    // Leer solo los encabezados de la primera fila
    $headers = [];
    
    // Usar getCellIterator para la primera fila
    $rowIterator = $sheet->getRowIterator(1, 1);
    foreach ($rowIterator as $row) {
        $cellIterator = $row->getCellIterator('A', $highestColumn);
        foreach ($cellIterator as $cell) {
            $value = $cell->getValue();
            if ($value === null || trim((string)$value) === '') {
                // Si encontramos una celda vacía, asumimos que terminaron los encabezados
                // pero continuamos para verificar
                continue;
            }
            $headers[] = trim((string)$value);
        }
        break; // Solo leer la primera fila
    }
    
    echo "Encabezados encontrados: " . count($headers) . "\n";
    echo "Primeros 5 encabezados:\n";
    foreach (array_slice($headers, 0, 5) as $h) {
        echo "  - $h\n";
    }
    
    // Contar filas de datos
    $dataRows = 0;
    $rowIterator = $sheet->getRowIterator(2, $highestRow);
    
    foreach ($rowIterator as $row) {
        $hasContent = false;
        foreach ($row->getCellIterator('A', end($headers) ? 'Z' : 'A') as $cell) {
            if ($cell->getValue() !== null && trim((string)$cell->getValue()) !== '') {
                $hasContent = true;
                break;
            }
        }
        if ($hasContent) {
            $dataRows++;
        }
    }
    
    echo "\nTotal de filas con datos: $dataRows\n";
    
    // Obtener un ejemplo de datos de la fila 2
    echo"\nEjemplo - Fila 2:\n";
    $row2 = $sheet->getRowIterator(2, 2)->current();
    $colIndex = 0;
    foreach ($row2->getCellIterator('A', 'Z') as $cell) {
        if ($colIndex >= count($headers)) break;
        $value = $cell->getValue();
        if ($value !== null) {
            echo $headers[$colIndex] . " = " . $value . "\n";
        }
        $colIndex++;
    }
    
    echo "\nÚltimos 5 encabezados:\n";
    foreach (array_slice($headers, -5) as $h) {
        echo "  - $h\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
