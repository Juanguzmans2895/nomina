<?php
require 'vendor/autoload.php';

use App\Modules\Nomina\Models\NovedadNomina;
use App\Modules\Nomina\Models\ConceptoNomina;
use App\Modules\RRHH\Models\Empleado;

// Simulate form submission data
$formData = [
    'empleado_id' => null,
    'concepto_id' => null,
    'fecha' => date('Y-m-d'),
    'cantidad' => 1,
    'valor_unitario' => 5000,
    'periodo_id' => null,
    'observaciones' => 'Test novedad',
    'procesada' => 1, // Checkbox checked (matches form name)
];

// Get first empleado and concepto
$empleado = Empleado::first();
$concepto = ConceptoNomina::first();

if (!$empleado || !$concepto) {
    die("No hay empleados o conceptos disponibles\n");
}

$formData['empleado_id'] = $empleado->id;
$formData['concepto_id'] = $concepto->id;

echo "Simulating form submission with processed checkbox...\n";
echo "Form data: " . json_encode($formData) . "\n\n";

// Simulate the controller logic
$validated = $formData;
$validated['valor_total'] = $validated['cantidad'] * $validated['valor_unitario'];

// This is the key line from controller - checking if 'procesada' key exists
// In Laravel, $request->has() returns true if the key exists in the request
$hasProcessada = isset($validated['procesada']) && $validated['procesada'];
$validated['estado'] = $hasProcessada ? 'aplicada' : 'pendiente';
$validated['created_by'] = 1;

// Remove the checkbox key so it doesn't interfere
unset($validated['procesada']);

echo "BEFORE create (estado will be): " . $validated['estado'] . "\n";

$novedad = NovedadNomina::create($validated);

echo "\nAFTER create:\n";
echo "DB Estado: " . $novedad->estado . "\n";
echo "Getter procesada: " . ($novedad->procesada ? 'true' : 'false') . "\n";
echo "In array: " . (isset($novedad->toArray()['procesada']) ? 'yes' : 'no') . "\n";

// Test update
$novedad->update(['estado' => 'pendiente']);
$novedad->refresh();

echo "\nAfter updating to 'pendiente':\n";
echo "DB Estado: " . $novedad->estado . "\n";
echo "Getter procesada: " . ($novedad->procesada ? 'true' : 'false') . "\n";
