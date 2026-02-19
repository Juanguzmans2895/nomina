<?php

namespace Database\Factories;

use App\Modules\Nomina\Models\TipoNomina;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipoNominaFactory extends Factory
{
    protected $model = TipoNomina::class;

    public function definition(): array
    {
        $nombre = $this->faker->randomElement([
            'Nómina Mensual',
            'Nómina Quincenal',
            'Prima de Servicios',
            'Bonificación',
            'Nómina Extraordinaria',
            'Cesantías',
            'Vacaciones',
        ]);

        return [
            'codigo' => strtoupper($this->faker->unique()->lexify('NOM-???')),
            'nombre' => $nombre,
            'descripcion' => 'Tipo de nómina: ' . $nombre,
            'periodicidad' => $this->faker->randomElement(['mensual', 'quincenal', 'semanal', 'eventual']),
            'activo' => true,
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }

    public function mensual(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => 'Nómina Mensual',
            'periodicidad' => 'mensual',
        ]);
    }

    public function quincenal(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => 'Nómina Quincenal',
            'periodicidad' => 'quincenal',
        ]);
    }
}