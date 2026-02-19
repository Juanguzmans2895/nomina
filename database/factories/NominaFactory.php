<?php

namespace Database\Factories;

use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Models\TipoNomina;
use App\Modules\Nomina\Models\PeriodoNomina;
use Illuminate\Database\Eloquent\Factories\Factory;

class NominaFactory extends Factory
{
    protected $model = Nomina::class;

    public function definition(): array
    {
        $tipoNomina = TipoNomina::inRandomOrder()->first() ?? TipoNomina::factory()->create();
        $periodo = PeriodoNomina::inRandomOrder()->first() ?? PeriodoNomina::factory()->create();
        
        $fechaInicio = $periodo->fecha_inicio;
        $fechaFin = $periodo->fecha_fin;
        
        $numeroEmpleados = $this->faker->numberBetween(10, 100);
        $totalDevengado = $numeroEmpleados * $this->faker->numberBetween(2000000, 5000000);
        $totalDeducciones = $totalDevengado * 0.12; // Aproximadamente 12%
        $totalNeto = $totalDevengado - $totalDeducciones;

        return [
            'numero_nomina' => sprintf('NOM-%s-%04d-%02d-%03d',
                strtoupper(substr($tipoNomina->nombre, 0, 3)),
                $periodo->anio,
                $periodo->mes,
                $this->faker->unique()->numberBetween(1, 999)
            ),
            'nombre' => $tipoNomina->nombre . ' - ' . $periodo->nombre,
            'descripcion' => $this->faker->optional(0.7)->sentence(),
            
            'tipo_nomina_id' => $tipoNomina->id,
            'periodo_nomina_id' => $periodo->id,
            
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'fecha_pago' => $this->faker->dateTimeBetween($fechaFin, '+10 days'),
            'fecha_corte' => $fechaFin,
            
            'numero_empleados' => $numeroEmpleados,
            'dias_liquidados' => 30,
            
            'total_devengado' => $totalDevengado,
            'total_deducciones' => $totalDeducciones,
            'total_neto' => $totalNeto,
            
            'total_salud_empleado' => $totalDevengado * 0.04,
            'total_salud_empleador' => $totalDevengado * 0.085,
            'total_pension_empleado' => $totalDevengado * 0.04,
            'total_pension_empleador' => $totalDevengado * 0.12,
            'total_arl_empleador' => $totalDevengado * 0.00522,
            
            'total_sena' => $totalDevengado * 0.02,
            'total_icbf' => $totalDevengado * 0.03,
            'total_caja' => $totalDevengado * 0.04,
            
            'total_cesantias' => $totalDevengado * 0.0833,
            'total_intereses_cesantias' => $totalDevengado * 0.01,
            'total_prima' => $totalDevengado * 0.0833,
            'total_vacaciones' => $totalDevengado * 0.0417,
            
            'costo_total_empleador' => $totalDevengado * 1.52, // Aproximadamente
            
            'estado' => $this->faker->randomElement(['borrador', 'aprobada']),
            
            'incluir_seguridad_social' => true,
            'incluir_parafiscales' => true,
            'incluir_provisiones' => true,
            'aplicar_retencion' => true,
            
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }

    public function aprobada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'aprobada',
            'aprobada_por' => 1,
            'fecha_aprobacion' => now(),
        ]);
    }

    public function pagada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'pagada',
            'aprobada_por' => 1,
            'fecha_aprobacion' => now()->subDays(5),
            'pagada_por' => 1,
            'fecha_pago_real' => now(),
        ]);
    }
}