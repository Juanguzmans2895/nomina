<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

$kernel->handle($request = \Illuminate\Http\Request::capture());

// Crear una novedad de prueba
$novedad = \App\Modules\Nomina\Models\NovedadNomina::create([
    'empleado_id' => 1,
    'concepto_id' => 1,
    'periodo_id' => 1,
    'tipo_novedad' => 'test',
    'fecha' => now(),
    'cantidad' => 1,
    'unidad' => 'unidad',
    'valor_unitario' => 100000,
    'valor_total' => 100000,
    'estado' => 'pendiente',
    'created_by' => 1,
]);

echo "✓ Novedad de prueba creada: ID=" . $novedad->id . "\n";
echo "  URL para probar: http://nomina.test/nomina/novedades/" . $novedad->id . "/editar\n";
echo "\nHaz clic en 'Aprobar Novedad' en la interfaz web para probar.\n";
