<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PeriodoNominaSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Generando períodos 2025-2027...');

        $insertados = 0;
        $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        for ($anio = 2025; $anio <= 2027; $anio++) {
            for ($mes = 1; $mes <= 12; $mes++) {
                $codigo = sprintf('%04d%02d', $anio, $mes);
                if (DB::table('periodos_nomina')->where('codigo', $codigo)->exists()) continue;

                $fechaInicio = Carbon::create($anio, $mes, 1);
                $fechaFin = $fechaInicio->copy()->endOfMonth();

                DB::table('periodos_nomina')->insert([
                    'codigo' => $codigo,
                    'nombre' => $meses[$mes] . ' ' . $anio,
                    'anio' => $anio,
                    'mes' => $mes,
                    'fecha_inicio' => $fechaInicio->toDateString(),
                    'fecha_fin' => $fechaFin->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $insertados++;
            }
        }

        $this->command->info("✅ Períodos creados: {$insertados}");
    }
}