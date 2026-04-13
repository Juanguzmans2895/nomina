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
        

        // Función auxiliar para determinar estado y datos asociados
        $generarEstado = function() {
            $rand = rand(1, 100);
            if ($rand <= 20) {
                // 20% Pendiente
                return [
                    'estado' => 'pendiente',
                    'aprobado_by' => null,
                    'fecha_aprobacion' => null,
                    'motivo_rechazo' => null,
                ];
            } elseif ($rand <= 85) {
                // 65% Aprobada
                return [
                    'estado' => 'aprobada',
                    'aprobado_by' => rand(1, 3), // Usuarios 1-3 aprueban
                    'fecha_aprobacion' => now()->subDays(rand(1, 10)),
                    'motivo_rechazo' => null,
                ];
            } else {
                // 15% Rechazada
                $motivos = [
                    'Documentación incompleta',
                    'Valor no corresponde al concepto',
                    'Empleado no está asignado a este concepto',
                    'Excede el límite permitido',
                    'Período ya procesado',
                    'Falta justificación',
                ];
                return [
                    'estado' => 'rechazada',
                    'aprobado_by' => rand(1, 3),
                    'fecha_aprobacion' => now()->subDays(rand(5, 15)),
                    'motivo_rechazo' => $motivos[rand(0, count($motivos) - 1)],
                ];
            }
        };
        
        foreach ($periodos as $periodo) {
            $fechaBase = Carbon::create($periodo['year'], $periodo['month'], 1);
            
            // ════ HORAS EXTRAS DIURNAS (30 novedades) ════
            for ($i = 0; $i < 5; $i++) {
                $empId = $empleadosPorTipo['produccion'][$i % count($empleadosPorTipo['produccion'])];
                $estado = $generarEstado();
                $novedades[] = [
                    'empleado_id' => $empId,
                    'concepto_id' => 2, // HED
                    'periodo_id' => $periodo['periodo_id'],
                    'nomina_id' => null,
                    'tipo_novedad' => 'hora_extra',
                    'fecha' => $fechaBase->copy()->addDays(rand(5, 25))->format('Y-m-d'),
                    'cantidad' => rand(4, 10),
                    'unidad' => 'horas',
                    'valor_unitario' => 15000,
                    'valor_total' => rand(60000, 150000),
                    'porcentaje_recargo' => 25.00,
                    'aplica_formula' => true,
                    'formula' => '(salario_basico/240)*1.25*cantidad',
                    'estado' => $estado['estado'],
                    'observaciones' => 'Horas extras diurnas - producción',
                    'archivo_soporte' => null,
                    'aprobado_by' => $estado['aprobado_by'],
                    'fecha_aprobacion' => $estado['fecha_aprobacion'],
                    'motivo_rechazo' => $estado['motivo_rechazo'],
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }
            
            // ════ HORAS EXTRAS NOCTURNAS (20 novedades) ════
            for ($i = 0; $i < 3; $i++) {
                $empId = $empleadosPorTipo['tech'][$i % count($empleadosPorTipo['tech'])];
                $estado = $generarEstado();
                $novedades[] = [
                    'empleado_id' => $empId,
                    'concepto_id' => 3, // HEN
                    'periodo_id' => $periodo['periodo_id'],
                    'nomina_id' => null,
                    'tipo_novedad' => 'hora_extra',
                    'fecha' => $fechaBase->copy()->addDays(rand(10, 28))->format('Y-m-d'),
                    'cantidad' => rand(2, 8),
                    'unidad' => 'horas',
                    'valor_unitario' => 20000,
                    'valor_total' => rand(40000, 160000),
                    'porcentaje_recargo' => 75.00,
                    'aplica_formula' => true,
                    'formula' => '(salario_basico/240)*1.75*cantidad',
                    'estado' => $estado['estado'],
                    'observaciones' => 'Horas extras nocturnas - soporte técnico',
                    'archivo_soporte' => null,
                    'aprobado_by' => $estado['aprobado_by'],
                    'fecha_aprobacion' => $estado['fecha_aprobacion'],
                    'motivo_rechazo' => $estado['motivo_rechazo'],
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }
            
            // ════ RECARGOS NOCTURNOS (15 novedades) ════
            for ($i = 0; $i < 3; $i++) {
                $empId = $empleadosPorTipo['produccion'][$i % count($empleadosPorTipo['produccion'])];
                $estado = $generarEstado();
                $novedades[] = [
                    'empleado_id' => $empId,
                    'concepto_id' => 8, // RN - Recargo nocturno
                    'periodo_id' => $periodo['periodo_id'],
                    'nomina_id' => null,
                    'tipo_novedad' => 'recargo',
                    'fecha' => $fechaBase->copy()->addDays(rand(1, 20))->format('Y-m-d'),
                    'cantidad' => rand(5, 15),
                    'unidad' => 'horas',
                    'valor_unitario' => 12000,
                    'valor_total' => rand(60000, 180000),
                    'porcentaje_recargo' => 35.00,
                    'aplica_formula' => true,
                    'formula' => '(salario_basico/240)*1.35*cantidad',
                    'estado' => $estado['estado'],
                    'observaciones' => 'Recargo nocturno',
                    'archivo_soporte' => null,
                    'aprobado_by' => $estado['aprobado_by'],
                    'fecha_aprobacion' => $estado['fecha_aprobacion'],
                    'motivo_rechazo' => $estado['motivo_rechazo'],
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }
            
            // ════ COMISIONES (25 novedades) ════
            for ($i = 0; $i < 5; $i++) {
                $empId = $empleadosPorTipo['vendedores'][$i % count($empleadosPorTipo['vendedores'])];
                $comision = rand(200000, 1200000);
                $estado = $generarEstado();
                $novedades[] = [
                    'empleado_id' => $empId,
                    'concepto_id' => 7, // COM
                    'periodo_id' => $periodo['periodo_id'],
                    'nomina_id' => null,
                    'tipo_novedad' => 'comision',
                    'fecha' => $fechaBase->copy()->endOfMonth()->subDays(2)->format('Y-m-d'),
                    'cantidad' => 1,
                    'unidad' => 'unidad',
                    'valor_unitario' => $comision,
                    'valor_total' => $comision,
                    'porcentaje_recargo' => null,
                    'aplica_formula' => false,
                    'formula' => null,
                    'estado' => $estado['estado'],
                    'observaciones' => 'Comisión por ventas del período',
                    'archivo_soporte' => null,
                    'aprobado_by' => $estado['aprobado_by'],
                    'fecha_aprobacion' => $estado['fecha_aprobacion'],
                    'motivo_rechazo' => $estado['motivo_rechazo'],
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }
            
            // ════ BONIFICACIONES (20 novedades) ════
            foreach ($empleadosPorTipo['ejecutivos'] as $empId) {
                if (rand(1, 100) <= 60) { // 60% probabilidad
                    $bono = rand(300000, 2000000);
                    $estado = $generarEstado();
                    $novedades[] = [
                        'empleado_id' => $empId,
                        'concepto_id' => 6, // BON
                        'periodo_id' => $periodo['periodo_id'],
                        'nomina_id' => null,
                        'tipo_novedad' => 'bonificacion',
                        'fecha' => $fechaBase->copy()->endOfMonth()->format('Y-m-d'),
                        'cantidad' => 1,
                        'unidad' => 'unidad',
                        'valor_unitario' => $bono,
                        'valor_total' => $bono,
                        'porcentaje_recargo' => null,
                        'aplica_formula' => false,
                        'formula' => null,
                        'estado' => $estado['estado'],
                        'observaciones' => 'Bonificación por metas cumplidas',
                        'archivo_soporte' => null,
                        'aprobado_by' => $estado['aprobado_by'],
                        'fecha_aprobacion' => $estado['fecha_aprobacion'],
                        'motivo_rechazo' => $estado['motivo_rechazo'],
                        'created_by' => null,
                        'updated_by' => null,
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
                    $dias = rand(1, 5);
                    $estado = $generarEstado();
                    $novedades[] = [
                        'empleado_id' => $empId,
                        'concepto_id' => 14, // INC
                        'periodo_id' => $periodo['periodo_id'],
                        'nomina_id' => null,
                        'tipo_novedad' => 'incapacidad',
                        'fecha' => $fechaBase->copy()->addDays(rand(5, 20))->format('Y-m-d'),
                        'cantidad' => $dias,
                        'unidad' => 'dias',
                        'valor_unitario' => 0,
                        'valor_total' => 0,
                        'porcentaje_recargo' => null,
                        'aplica_formula' => false,
                        'formula' => null,
                        'estado' => $estado['estado'],
                        'observaciones' => 'Incapacidad médica - ' . $dias . ' días',
                        'archivo_soporte' => null,
                        'aprobado_by' => $estado['aprobado_by'],
                        'fecha_aprobacion' => $estado['fecha_aprobacion'],
                        'motivo_rechazo' => $estado['motivo_rechazo'],
                        'created_by' => null,
                        'updated_by' => null,
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
                    $cuota = rand(50000, 500000);
                    $estado = $generarEstado();
                    $novedades[] = [
                        'empleado_id' => $empId,
                        'concepto_id' => 12, // CREDITO
                        'periodo_id' => $periodo['periodo_id'],
                        'nomina_id' => null,
                        'tipo_novedad' => 'descuento',
                        'fecha' => $fechaBase->copy()->addDays(1)->format('Y-m-d'),
                        'cantidad' => 1,
                        'unidad' => 'unidad',
                        'valor_unitario' => $cuota,
                        'valor_total' => $cuota,
                        'porcentaje_recargo' => null,
                        'aplica_formula' => false,
                        'formula' => null,
                        'estado' => $estado['estado'],
                        'observaciones' => 'Cuota mensual de crédito',
                        'archivo_soporte' => null,
                        'aprobado_by' => $estado['aprobado_by'],
                        'fecha_aprobacion' => $estado['fecha_aprobacion'],
                        'motivo_rechazo' => $estado['motivo_rechazo'],
                        'created_by' => null,
                        'updated_by' => null,
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