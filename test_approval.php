<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

$kernel->handle($request = \Illuminate\Http\Request::capture());

$novedades = \App\Modules\Nomina\Models\NovedadNomina::where('estado', 'pendiente')->first();

if ($novedades) {
    echo "✓ Novedad encontrada: ID=" . $novedades->id . ", Estado=" . $novedades->estado . "\n";
    echo "  Empleado: " . ($novedades->empleado ? $novedades->empleado->nombre_completo : 'Sin empleado') . "\n";
    echo "  Concepto: " . ($novedades->concepto ? $novedades->concepto->nombre : 'Sin concepto') . "\n\n";
    
    echo "Intentando aprobar con usuario ID=1...\n";
    $result = $novedades->aprobar(1);
    echo "Resultado del método: " . ($result ? 'true' : 'false') . "\n";
    
    $fresh = \App\Modules\Nomina\Models\NovedadNomina::find($novedades->id);
    echo "Estado en BD ahora: " . $fresh->estado . "\n";
    echo "Aprobado por: " . $fresh->aprobado_by . "\n";
    echo "Fecha aprobación: " . $fresh->fecha_aprobacion . "\n";
} else {
    echo "✗ No hay novedades pendientes\n";
    $counts = \App\Modules\Nomina\Models\NovedadNomina::selectRaw('estado, COUNT(*) as count')->groupBy('estado')->get();
    echo "\nNovedades por estado:\n";
    foreach ($counts as $row) {
        echo "  " . $row->estado . ": " . $row->count . "\n";
    }
}
