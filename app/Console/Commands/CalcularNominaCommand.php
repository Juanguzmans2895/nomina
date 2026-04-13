<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\PeriodoNomina;
use App\Services\NominaCalculoService;

class CalcularNominaCommand extends Command
{
    protected $signature = 'nomina:calcular {periodo_id?} {empleado_id?}';
    protected $description = 'Calcular nómina para un período o empleado específico';

    public function handle()
    {
        $periodoId = $this->argument('periodo_id');
        $empleadoId = $this->argument('empleado_id');

        $periodo = PeriodoNomina::findOrFail($periodoId ?? PeriodoNomina::latest()->first()->id);
        
        $this->info("=== CÁLCULO DE NÓMINA ===");
        $this->line("Período: {$periodo->nombre}");

        // Obtener o crear nómina para el período
        $nomina = Nomina::firstOrCreate(
            ['periodo_nomina_id' => $periodo->id],
            [
                'numero_nomina' => 'NOM-' . $periodo->codigo,
                'nombre' => "Nómina {$periodo->nombre}",
                'tipo_nomina_id' => 1,
                'fecha_inicio' => $periodo->fecha_inicio,
                'fecha_fin' => $periodo->fecha_fin,
                'estado' => 'prenomina',
            ]
        );

        $empleados = Empleado::where('estado', 'activo');
        if ($empleadoId) {
            $empleados = $empleados->where('id', $empleadoId);
        }

        $empleados = $empleados->get();
        $servicio = new NominaCalculoService();

        $bar = $this->output->createProgressBar($empleados->count());
        $totalNeto = 0;

        foreach ($empleados as $empleado) {
            $detalle = $servicio->calcularNomina($empleado, $periodo, $nomina);
            $totalNeto += $detalle->total_neto;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Actualizar nómina con totales
        $nomina->update([
            'total_neto' => $totalNeto,
            'numero_empleados' => $empleados->count(),
            'estado' => 'aprobada',
        ]);

        $this->info("✅ Nómina calculada para {$empleados->count()} empleados");
        $this->info("💰 Total Neto a Pagar: $" . number_format($totalNeto, 2));
    }
}
