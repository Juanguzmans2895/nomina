<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoNominaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            [
                'nombre' => 'Nómina Mensual',
                'descripcion' => 'Nómina ordinaria mensual para empleados',
                'codigo' => 'NOM',
                'periodicidad' => 'mensual',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Prima de Servicios',
                'descripcion' => 'Prima de servicios semestral',
                'codigo' => 'PRI',
                'periodicidad' => 'semestral',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Cesantías',
                'descripcion' => 'Liquidación de cesantías',
                'codigo' => 'CES',
                'periodicidad' => 'anual',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Vacaciones',
                'descripcion' => 'Pago de vacaciones',
                'codigo' => 'VAC',
                'periodicidad' => 'variable',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Bonificaciones',
                'descripcion' => 'Bonificaciones extraordinarias',
                'codigo' => 'BON',
                'periodicidad' => 'variable',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Liquidación Final',
                'descripcion' => 'Liquidación al retiro del empleado',
                'codigo' => 'LIQ',
                'periodicidad' => 'variable',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('tipos_nomina')->insert($tipos);
    }
}