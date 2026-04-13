<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Modules\Nomina\Models\NovedadNomina;

echo "TEST 1: Create con estado='aplicada'\n";
$nov = NovedadNomina::create([
    'empleado_id' => 1,
    'concepto_id' => 1,
    'periodo_id' => 6,
    'tipo_novedad' => 'test',
    'fecha' => now(),
    'cantidad' => 1,
    'unidad' => 'test',
    'valor_unitario' => 100,
    'valor_total' => 100,
    'estado' => 'aplicada',
    'created_by' => 1
]);

$fetched = NovedadNomina::find($nov->id);
echo "ID: " . $fetched->id . "\n";
echo "Estado BD: " . $fetched->estado . "\n";
echo "Procesada (getter): " . ($fetched->procesada ? 'true' : 'false') . "\n";
echo "En appends: " . (in_array('procesada', $fetched->getAppends()) ? 'yes' : 'no') . "\n";
echo "En toArray: " . (isset($fetched->toArray()['procesada']) ? 'yes' : 'no') . "\n";

$fetched->delete();

echo "\nTEST 2: Actualizar a estado='pendiente'\n";
$nov2 = NovedadNomina::create([
    'empleado_id' => 1,
    'concepto_id' => 1,
    'periodo_id' => 6,
    'tipo_novedad' => 'test',
    'fecha' => now(),
    'cantidad' => 1,
    'unidad' => 'test',
    'valor_unitario' => 100,
    'valor_total' => 100,
    'estado' => 'pendiente',
    'created_by' => 1
]);

$nov2->update(['estado' => 'aplicada']);
$updated = NovedadNomina::find($nov2->id);
echo "Después update a aplicada:\n";
echo "Estado BD: " . $updated->estado . "\n";
echo "Procesada: " . ($updated->procesada ? 'true' : 'false') . "\n";

$updated->delete();

echo "\n✓ Tests completados\n";
