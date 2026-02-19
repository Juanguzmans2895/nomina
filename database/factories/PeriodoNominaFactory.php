<?php

namespace Database\Factories;

use App\Modules\Nomina\Models\PeriodoNomina;
use Illuminate\Database\Eloquent\Factories\Factory;

class PeriodoNominaFactory extends Factory
{
    protected $model = PeriodoNomina::class;

    public function definition(): array
    {
        $anio = $this->faker->numberBetween(2023, 2025);
        $mes = $this->faker->numberBetween(1, 12);
        
        $fechaInicio = \Carbon\Carbon::create($anio, $mes, 1);
        $fechaFin = $fechaInicio->copy()->endOfMonth();

        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        return [
            'anio' => $anio,
            'mes' => $mes,
            'nombre' => $meses[$mes] . ' ' . $anio,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'estado' => $this->faker->randomElement(['abierto', 'cerrado']),
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }

    public function abierto(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'abierto',
        ]);
    }

    public function cerrado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'cerrado',
            'cerrado_por' => 1,
            'fecha_cierre' => now(),
        ]);
    }

    public function mesActual(): static
    {
        return $this->state(fn (array $attributes) => [
            'anio' => now()->year,
            'mes' => now()->month,
            'nombre' => now()->locale('es')->monthName . ' ' . now()->year,
            'fecha_inicio' => now()->startOfMonth(),
            'fecha_fin' => now()->endOfMonth(),
            'estado' => 'abierto',
        ]);
    }
}