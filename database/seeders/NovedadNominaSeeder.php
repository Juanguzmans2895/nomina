<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NovedadNominaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📝 Creando novedades de nómina...');
        
        $novedades = [];
        $count = 0;
        
        // Empleados y sus características para distribución de novedades
        $empleadosPorTipo = [
            'produccion' => [38, 39, 40, 41, 42, 43, 44],
            'vendedores' => [24, 25, 26, 27, 28, 29, 30, 31, 32],
            'tech' => [15, 16, 17, 18, 19, 20, 21, 22],
            'ejecutivos' => [1, 2, 3],
            'todos' => range(1, 50),
        ];
        
        // Periodos para crear novedades (últimos 6 meses)
        $periodos = [
            ['periodo_id' => 6, 'month' => 1, 'year' => 2026, 'mes' => 'Enero 2026'],
            ['periodo_id' => 7, 'month' => 2, 'year' => 2026, 'mes' => 'Febrero 2026'],
            ['periodo_id' => 8, 'month' => 3, 'year' => 2026, 'mes' => 'Marzo 2026'],
            ['periodo_id' => 9, 'month' => 4, 'year' => 2026, 'mes' => 'Abril 2026'],
            ['periodo_id' => 10, 'month' => 5, 'year' => 2026, 'mes' => 'Mayo 2026'],
            ['periodo_id' => 11, 'month' => 6, 'year' => 2026, 'mes' => 'Junio 2026'],
        ];
        
        foreach ($periodos as $periodo) {
            $fechaBase = Carbon::create($periodo['year'], $periodo['month'], 1);
            
            // ════ HORAS EXTRAS DIURNAS (30 novedades) ════
            for ($i = 0; $i < 5; $i++) {
                $empId = $empleadosPorTipo['produccion'][$i % count($empleadosPorTipo['produccion'])];
                $novedades[] = [
                    'empleado_id' => $empId,
                    'concepto_nomina_id' => 2, // HED
                    'periodo_nomina_id' => $periodo['periodo_id'],
                    'fecha_novedad' => $fechaBase->copy()->addDays(rand(5, 25))->format('Y-m-d'),
                    'cantidad' => rand(4, 10),
                    'valor_unitario' => 15000,
                    'valor_total' => rand(60000, 150000),
                    'observaciones' => 'Horas extras diurnas - producción',
                    'procesada' => rand(1, 100) <= 70, // 70% ya procesadas
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }
            
            // ════ HORAS EXTRAS NOCTURNAS (20 novedades) ════
            for ($i = 0; $i < 3; $i++) {
                $empId = $empleadosPorTipo['tech'][$i % count($empleadosPorTipo['tech'])];
                $novedades[] = [
                    'empleado_id' => $empId,
                    'concepto_nomina_id' => 3, // HEN
                    'periodo_nomina_id' => $periodo['periodo_id'],
                    'fecha_novedad' => $fechaBase->copy()->addDays(rand(10, 28))->format('Y-m-d'),
                    'cantidad' => rand(2, 8),
                    'valor_unitario' => 20000,
                    'valor_total' => rand(40000, 160000),
                    'observaciones' => 'Horas extras nocturnas - soporte técnico',
                    'procesada' => rand(1, 100) <= 70,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }
            
            // ════ RECARGOS NOCTURNOS (15 novedades) ════
            for ($i = 0; $i < 3; $i++) {
                $empId = $empleadosPorTipo['produccion'][$i % count($empleadosPorTipo['produccion'])];
                $novedades[] = [
                    'empleado_id' => $empId,
                    'concepto_nomina_id' => 8, // RN - Recargo nocturno
                    'periodo_nomina_id' => $periodo['periodo_id'],
                    'fecha_novedad' => $fechaBase->copy()->addDays(rand(1, 20))->format('Y-m-d'),
                    'cantidad' => rand(5, 15),
                    'valor_unitario' => 12000,
                    'valor_total' => rand(60000, 180000),
                    'observaciones' => 'Recargo nocturno',
                    'procesada' => rand(1, 100) <= 70,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }
            
            // ════ COMISIONES (25 novedades) ════
            for ($i = 0; $i < 5; $i++) {
                $empId = $empleadosPorTipo['vendedores'][$i % count($empleadosPorTipo['vendedores'])];
                $novedades[] = [
                    'empleado_id' => $empId,
                    'concepto_nomina_id' => 7, // COM
                    'periodo_nomina_id' => $periodo['periodo_id'],
                    'fecha_novedad' => $fechaBase->copy()->endOfMonth()->subDays(2)->format('Y-m-d'),
                    'cantidad' => 1,
                    'valor_unitario' => rand(200000, 1200000),
                    'valor_total' => rand(200000, 1200000),
                    'observaciones' => 'Comisión por ventas del período',
                    'procesada' => rand(1, 100) <= 70,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }
            
            // ════ BONIFICACIONES (20 novedades) ════
            foreach ($empleadosPorTipo['ejecutivos'] as $empId) {
                if (rand(1, 100) <= 60) { // 60% probabilidad
                    $novedades[] = [
                        'empleado_id' => $empId,
                        'concepto_nomina_id' => 6, // BON
                        'periodo_nomina_id' => $periodo['periodo_id'],
                        'fecha_novedad' => $fechaBase->copy()->endOfMonth()->format('Y-m-d'),
                        'cantidad' => 1,
                        'valor_unitario' => rand(300000, 2000000),
                        'valor_total' => rand(300000, 2000000),
                        'observaciones' => 'Bonificación por metas cumplidas',
                        'procesada' => rand(1, 100) <= 70,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $count++;
                }
            }
            
            // ════ INCAPACIDADES (15 novedades) ════
            for ($i = 0; $i < 2; $i++) {
                if (rand(1, 100) <= 40) { // 40% probabilidad
                    $empId = $empleadosPorTipo['todos'][rand(0, count($empleadosPorTipo['todos']) - 1)];
                    $novedades[] = [
                        'empleado_id' => $empId,
                        'concepto_nomina_id' => 14, // INC
                        'periodo_nomina_id' => $periodo['periodo_id'],
                        'fecha_novedad' => $fechaBase->copy()->addDays(rand(5, 20))->format('Y-m-d'),
                        'cantidad' => rand(1, 5),
                        'valor_unitario' => 0,
                        'valor_total' => 0,
                        'observaciones' => 'Incapacidad médica - ' . rand(1, 5) . ' días',
                        'procesada' => rand(1, 100) <= 70,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $count++;
                }
            }
            
            // ════ PRÉSTAMOS / DESCUENTOS (10 novedades) ════
            for ($i = 0; $i < 2; $i++) {
                if (rand(1, 100) <= 30) { // 30% probabilidad
                    $empId = $empleadosPorTipo['todos'][rand(0, count($empleadosPorTipo['todos']) - 1)];
                    $novedades[] = [
                        'empleado_id' => $empId,
                        'concepto_nomina_id' => 12, // CREDITO
                        'periodo_nomina_id' => $periodo['periodo_id'],
                        'fecha_novedad' => $fechaBase->copy()->addDays(1)->format('Y-m-d'),
                        'cantidad' => 1,
                        'valor_unitario' => rand(50000, 500000),
                        'valor_total' => rand(50000, 500000),
                        'observaciones' => 'Cuota mensual de crédito',
                        'procesada' => rand(1, 100) <= 70,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $count++;
                }
            }
        }
        
        DB::table('novedades_nomina')->insert($novedades);
        
        $this->command->info("✓ $count novedades de nómina creadas exitosamente");
    }
}