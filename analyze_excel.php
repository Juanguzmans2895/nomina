<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load('LIQUIDACION DE NOMINA ENERO 2026.xlsx');
$worksheet = $spreadsheet->getActiveSheet();

echo 'SHEET: ' . $worksheet->getTitle() . "\n\n";
echo "=== ESTRUCTURA DEL DOCUMENTO ===\n\n";

for ($row = 1; $row <= 60; $row++) {
    $rowData = [];
    for ($col = 1; $col <= 15; $col++) {
        $cell = $worksheet->getCellByColumnAndRow($col, $row);
        $val = $cell->getValue();
        if ($val === null) $val = '';
        $rowData[] = $val;
    }
    $hasData = implode('', $rowData) !== '';
    if ($hasData) {
        echo 'Row ' . $row . ': ' . implode(' | ', $rowData) . "\n";
    }
}
?>
