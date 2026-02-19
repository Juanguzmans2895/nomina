<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class PeriodoNominaSeeder extends Seeder
{
    public function run(): void
    {
        // Detectar qué columnas existen en la tabla
        $columns = Schema::getColumnListing('periodos_nomina');
        
        $hasCerrado = in_array('cerrado', $columns);
        $hasEstado = in_array('estado', $columns);
        $hasCodigo = in_array('codigo', $columns);
        $hasPeriodicidad = in_array('periodicidad', $columns);
        
        $periodos = [];
        
        // Crear 36 períodos: 2024 (12), 2025 (12), 2026 (12)
        // 2024: todos cerrados
        // 2025: todos cerrados
        // 2026: enero-marzo cerrados, resto abiertos
        
        for ($year = 2024; $year <= 2026; $year++) {
            for ($mes = 1; $mes <= 12; $mes++) {
                $fechaInicio = Carbon::create($year, $mes, 1);
                $fechaFin = $fechaInicio->copy()->endOfMonth();
                
                // Determinar si está cerrado
                $estaCerrado = ($year < 2026) || ($year == 2026 && $mes <= 3);
                
                $periodo = [
                    'nombre' => ucfirst($fechaInicio->locale('es')->isoFormat('MMMM YYYY')),
                    'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                    'fecha_fin' => $fechaFin->format('Y-m-d'),
                    'anio' => $year,
                    'mes' => $mes,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                // Agregar campos opcionales según existan
                if ($hasCodigo) {
                    $periodo['codigo'] = sprintf('PER-%d-%02d', $year, $mes);
                }
                
                if ($hasCerrado) {
                    $periodo['cerrado'] = $estaCerrado;
                    if ($estaCerrado) {
                        $periodo['fecha_cierre'] = $fechaFin->copy()->addDays(5);
                    }
                }
                
                if ($hasEstado) {
                    $periodo['estado'] = $estaCerrado ? 'cerrado' : 'abierto';
                }
                
                if ($hasPeriodicidad) {
                    $periodo['periodicidad'] = 'mensual';
                }
                
                $periodos[] = $periodo;
            }
        }

        DB::table('periodos_nomina')->insert($periodos);
        
        $this->command->info("✓ $" . count($periodos) . " períodos de nómina creados (2024-2026)");
    }
}