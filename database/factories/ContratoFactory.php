<?php

namespace Database\Factories;

use App\Modules\Nomina\Models\Contrato;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\CentroCosto;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContratoFactory extends Factory
{
    protected $model = Contrato::class;

    public function definition(): array
    {
        $fechaInicio = $this->faker->dateTimeBetween('-1 year', 'now');
        $plazo = $this->faker->numberBetween(30, 365);
        $fechaFin = (clone $fechaInicio)->modify("+{$plazo} days");
        
        $valorTotal = $this->faker->randomElement([
            5000000, 8000000, 10000000, 15000000, 20000000,
            25000000, 30000000, 50000000, 75000000, 100000000
        ]);

        return [
            'numero_contrato' => 'CT-' . $this->faker->unique()->numerify('####-####'),
            'tipo_contrato' => $this->faker->randomElement(['prestacion_servicios', 'obra_labor', 'suministro']),
            'objeto' => $this->faker->sentence(15),
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'plazo_dias' => $plazo,
            
            'tipo_documento_contratista' => $this->faker->randomElement(['CC', 'CE', 'NIT']),
            'numero_documento_contratista' => $this->faker->numerify('##########'),
            'nombre_contratista' => $this->faker->name(),
            'direccion_contratista' => $this->faker->address(),
            'telefono_contratista' => $this->faker->numerify('3#########'),
            'email_contratista' => $this->faker->safeEmail(),
            
            'valor_total' => $valorTotal,
            'valor_mensual' => round($valorTotal / ceil($plazo / 30), 2),
            'saldo_pendiente' => $valorTotal,
            'anticipo' => $this->faker->optional(0.3)->randomElement([
                $valorTotal * 0.2,
                $valorTotal * 0.3,
            ]),
            'porcentaje_anticipo' => $this->faker->optional(0.3)->randomElement([20, 30]),
            
            'aplica_retencion_fuente' => true,
            'porcentaje_retencion_fuente' => $this->faker->randomElement([10.0, 11.0, 4.0]),
            'aplica_estampilla' => $this->faker->boolean(20),
            'porcentaje_estampilla' => $this->faker->optional(0.2)->randomElement([1.0, 1.5]),
            
            'supervisor_id' => $this->faker->optional(0.7)->randomElement(
                Empleado::where('estado', 'activo')->pluck('id')->toArray() ?: [null]
            ),
            'centro_costo_id' => $this->faker->optional(0.8)->randomElement(
                CentroCosto::where('activo', true)->pluck('id')->toArray() ?: [null]
            ),
            
            'estado' => $this->faker->randomElement(['aprobado', 'en_ejecucion']),
            'requiere_polizas' => $this->faker->boolean(80),
            'requiere_afiliacion_seguridad_social' => $this->faker->boolean(60),
            
            'observaciones' => $this->faker->optional(0.5)->sentence(),
            
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }

    public function borrador(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'borrador',
        ]);
    }

    public function enEjecucion(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'en_ejecucion',
            'fecha_inicio' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    public function terminado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'terminado',
            'fecha_fin' => $this->faker->dateTimeBetween('-3 months', 'now'),
        ]);
    }
}