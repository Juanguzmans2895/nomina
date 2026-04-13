<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->loadEnvironmentFrom(base_path('.env'));

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Obtener instancia de base de datos
$db = $app->makeWith('db', []);

// Verificar nóminas
echo "=== VERIFICACIÓN DE NÓMINAS ===\n\n";

// Contar registros
$countNominas = $db->table('nominas')->count();
$countDetalles = $db->table('detalle_nominas')->count();
$countConceptos = $db->table('concepto_nominas')->count();

echo "Total de Nóminas: $countNominas\n";
echo "Total de Detalles: $countDetalles\n";
echo "Total de Conceptos: $countConceptos\n\n";

// Mostrar detalle de primera nómina
$nomina = $db->table('nominas')->first();
if ($nomina) {
    echo "=== PRIMERA NÓMINA ===\n";
    echo "ID: {$nomina->id}\n";
    echo "Número: {$nomina->numero_nomina}\n";
    echo "Total Neto: $" . number_format($nomina->total_neto, 2) . "\n";
    echo "Empleados: {$nomina->numero_empleados}\n\n";
    
    // Mostrar primer detalle
    $detalle = $db->table('detalle_nominas')
        ->where('nomina_id', $nomina->id)
        ->first();
    
    if ($detalle) {
        echo "=== PRIMER EMPLEADO ===\n";
        echo "Salario Base: $" . number_format($detalle->salario_base, 2) . "\n";
        echo "Días Laborados: {$detalle->dias_laborados}\n";
        echo "Total Devengado: $" . number_format($detalle->total_devengado, 2) . "\n";
        echo "Deducción Salud: $" . number_format($detalle->deduccion_salud ?? 0, 2) . "\n";
        echo "Deducción Pensión: $" . number_format($detalle->deduccion_pension ?? 0, 2) . "\n";
        echo "Total Deducciones: $" . number_format($detalle->total_deducciones, 2) . "\n";
        echo "Total Neto: $" . number_format($detalle->total_neto, 2) . "\n";
        echo "Cesantías: $" . number_format($detalle->cesantias ?? 0, 2) . "\n";
        echo "Prima: $" . number_format($detalle->prima ?? 0, 2) . "\n";
        echo "Vacaciones: $" . number_format($detalle->vacaciones ?? 0, 2) . "\n";
    }
}

echo "\n✅ Verificación completada\n";
