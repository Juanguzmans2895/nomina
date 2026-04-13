<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'C:\\Users\\jguzm\\Downloads\\LIQUIDACION DE NOMINA ENERO 2026.xlsx';

try {
    echo "Intentando cargar archivo: $filePath\n";
    $spreadsheet = IOFactory::load($filePath);
    echo "Archivo cargado exitosamente\n";
    
    $sheet = $spreadsheet->getActiveSheet();
    echo "Hoja activa: " . $sheet->getTitle() . "\n";
    
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    
    echo "Datos encontrados: Filas hasta $highestRow, Columnas hasta $highestColumn\n";
    
    // Mostrarsolo los encabezados
    echo "\nEncabezados (Primera fila):\n";
    for ($col = 1; $col <= 20; $col++) {
        $cell = $sheet->getCellByColumnAndRow($col, 1);
        $value = $cell->getValue();
        if ($value === null) break;
        echo "Columna $col: " . trim((string)$value) . "\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Trace:\n";
    echo $e->getTraceAsString() . "\n";
}
