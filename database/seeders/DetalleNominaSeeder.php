<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DetalleNominaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * NOTA: Usa tabla 'nomina_detalles' (según migration 2026_01_31_154359)
     */
    public function run(): void
    {
        $this->command->info('📋 Creando detalles de nómina (nomina_detalles)...');
        
        // 50 empleados con sus salarios (coinciden con EmpleadoSeeder)
        $empleados = [
            // === GERENCIA (3)
            ['id' => 1, 'salario' => 12000000, 'depto' => 'Gerencia', 'es_vendedor' => false],
            ['id' => 2, 'salario' => 10000000, 'depto' => 'Gerencia', 'es_vendedor' => false],
            ['id' => 3, 'salario' => 11000000, 'depto' => 'Gerencia', 'es_vendedor' => false],
            
            // === CONTABILIDAD (6)
            ['id' => 4, 'salario' => 6500000, 'depto' => 'Contabilidad', 'es_vendedor' => false],
            ['id' => 5, 'salario' => 4500000, 'depto' => 'Contabilidad', 'es_vendedor' => false],
            ['id' => 6, 'salario' => 3800000, 'depto' => 'Contabilidad', 'es_vendedor' => false],
            ['id' => 7, 'salario' => 3200000, 'depto' => 'Contabilidad', 'es_vendedor' => false],
            ['id' => 8, 'salario' => 5500000, 'depto' => 'Contabilidad', 'es_vendedor' => false],
            ['id' => 9, 'salario' => 5000000, 'depto' => 'Contabilidad', 'es_vendedor' => false],
            
            // === RECURSOS HUMANOS (5)
            ['id' => 10, 'salario' => 7000000, 'depto' => 'RRHH', 'es_vendedor' => false],
            ['id' => 11, 'salario' => 4200000, 'depto' => 'RRHH', 'es_vendedor' => false],
            ['id' => 12, 'salario' => 4800000, 'depto' => 'RRHH', 'es_vendedor' => false],
            ['id' => 13, 'salario' => 3000000, 'depto' => 'RRHH', 'es_vendedor' => false],
            ['id' => 14, 'salario' => 4500000, 'depto' => 'RRHH', 'es_vendedor' => false],
            
            // === TECNOLOGÍA (8)
            ['id' => 15, 'salario' => 9000000, 'depto' => 'Tecnología', 'es_vendedor' => false],
            ['id' => 16, 'salario' => 7000000, 'depto' => 'Tecnología', 'es_vendedor' => false],
            ['id' => 17, 'salario' => 6000000, 'depto' => 'Tecnología', 'es_vendedor' => false],
            ['id' => 18, 'salario' => 5500000, 'depto' => 'Tecnología', 'es_vendedor' => false],
            ['id' => 19, 'salario' => 5500000, 'depto' => 'Tecnología', 'es_vendedor' => false],
            ['id' => 20, 'salario' => 6500000, 'depto' => 'Tecnología', 'es_vendedor' => false],
            ['id' => 21, 'salario' => 4800000, 'depto' => 'Tecnología', 'es_vendedor' => false],
            ['id' => 22, 'salario' => 3500000, 'depto' => 'Tecnología', 'es_vendedor' => false],
            
            // === COMERCIAL / VENTAS (10)
            ['id' => 23, 'salario' => 8500000, 'depto' => 'Comercial', 'es_vendedor' => false],
            ['id' => 24, 'salario' => 5500000, 'depto' => 'Ventas', 'es_vendedor' => true],
            ['id' => 25, 'salario' => 4800000, 'depto' => 'Ventas', 'es_vendedor' => true],
            ['id' => 26, 'salario' => 4200000, 'depto' => 'Ventas', 'es_vendedor' => true],
            ['id' => 27, 'salario' => 4000000, 'depto' => 'Ventas', 'es_vendedor' => true],
            ['id' => 28, 'salario' => 3800000, 'depto' => 'Ventas', 'es_vendedor' => true],
            ['id' => 29, 'salario' => 3600000, 'depto' => 'Ventas', 'es_vendedor' => true],
            ['id' => 30, 'salario' => 3200000, 'depto' => 'Ventas', 'es_vendedor' => true],
            ['id' => 31, 'salario' => 3000000, 'depto' => 'Ventas', 'es_vendedor' => true],
            ['id' => 32, 'salario' => 1500000, 'depto' => 'Ventas', 'es_vendedor' => true],
            
            // === MARKETING (5)
            ['id' => 33, 'salario' => 7500000, 'depto' => 'Marketing', 'es_vendedor' => false],
            ['id' => 34, 'salario' => 5000000, 'depto' => 'Marketing', 'es_vendedor' => false],
            ['id' => 35, 'salario' => 4000000, 'depto' => 'Marketing', 'es_vendedor' => false],
            ['id' => 36, 'salario' => 3800000, 'depto' => 'Marketing', 'es_vendedor' => false],
            ['id' => 37, 'salario' => 2800000, 'depto' => 'Marketing', 'es_vendedor' => false],
            
            // === PRODUCCIÓN (7)
            ['id' => 38, 'salario' => 6500000, 'depto' => 'Producción', 'es_vendedor' => false],
            ['id' => 39, 'salario' => 4800000, 'depto' => 'Producción', 'es_vendedor' => false],
            ['id' => 40, 'salario' => 3500000, 'depto' => 'Producción', 'es_vendedor' => false],
            ['id' => 41, 'salario' => 3500000, 'depto' => 'Producción', 'es_vendedor' => false],
            ['id' => 42, 'salario' => 2600000, 'depto' => 'Producción', 'es_vendedor' => false],
            ['id' => 43, 'salario' => 2600000, 'depto' => 'Producción', 'es_vendedor' => false],
            ['id' => 44, 'salario' => 1800000, 'depto' => 'Producción', 'es_vendedor' => false],
            
            // === LOGÍSTICA (6)
            ['id' => 45, 'salario' => 6000000, 'depto' => 'Logística', 'es_vendedor' => false],
            ['id' => 46, 'salario' => 4200000, 'depto' => 'Logística', 'es_vendedor' => false],
            ['id' => 47, 'salario' => 3500000, 'depto' => 'Logística', 'es_vendedor' => false],
            ['id' => 48, 'salario' => 3200000, 'depto' => 'Logística', 'es_vendedor' => false],
            ['id' => 49, 'salario' => 3000000, 'depto' => 'Logística', 'es_vendedor' => false],
            ['id' => 50, 'salario' => 2500000, 'depto' => 'Logística', 'es_vendedor' => false],
        ];
        
        $detalles = [];
        $count = 0;
        
        // Obtener las 6 nóminas más recientes de 2026 (enero a junio 2026)
        $nominas = DB::table('nominas')
            ->where('fecha_inicio', '>=', Carbon::create(2026, 1, 1))
            ->where('fecha_inicio', '<=', Carbon::create(2026, 6, 30))
            ->orderBy('fecha_inicio')
            ->get();
        
        if ($nominas->isEmpty()) {
            $this->command->warn('⚠️ No hay nóminas para crear detalles');
            return;
        }
        
        $this->command->line("Generando detalles para " . $nominas->count() . " nóminas...");
        
        foreach ($nominas as $nomina) {
            foreach ($empleados as $emp) {
                $salario = $emp['salario'];
                $esVendedor = $emp['es_vendedor'];
                
                // Calcular devengados variables según el mes
                $horasExtrasDiurnas = 0;
                $horasExtrasNocturnas = 0;
                $recargos = 0;
                $comisiones = 0;
                $bonificaciones = 0;
                
                // Novedades aleatorias pero coherentes
                if ($emp['depto'] == 'Producción') {
                    // Producción tiene más horas extras
                    if (rand(1, 100) <= 60) { // 60% probabilidad
                        $horasExtrasDiurnas = rand(4, 12) * 15000; // 4-12 horas × $15k
                    }
                    if (rand(1, 100) <= 30) { // 30% probabilidad
                        $horasExtrasNocturnas = rand(2, 8) * 20000; // 2-8 horas × $20k
                    }
                }
                
                if ($emp['depto'] == 'Tecnología' && rand(1, 100) <= 40) {
                    // Tech tiene horas extras nocturnas ocasionales
                    $horasExtrasNocturnas = rand(2, 6) * 20000;
                }
                
                if ($esVendedor) {
                    // Vendedores tienen comisiones variadas por mes
                    if (rand(1, 100) <= 80) { // 80% de probabilidad
                        $comisiones = $salario * rand(5, 20) / 100; // 5-20% de comisión
                    }
                    if (rand(1, 100) <= 25) { // 25% probabilidad
                        $bonificaciones = rand(200000, 800000);
                    }
                }
                
                // Ejecutivos ocasionalmente reciben bonificaciones
                if ($emp['depto'] == 'Gerencia' && rand(1, 100) <= 50) {
                    $bonificaciones = rand(500000, 2000000);
                }
                
                // Auxilio de transporte (si aplica)
                $auxilioTransporte = ($salario <= 2600000) ? 162000 : 0;
                
                // Calcular totales devengados
                $totalDevengado = $salario + $auxilioTransporte + $horasExtrasDiurnas + 
                                 $horasExtrasNocturnas + $recargos + $comisiones + $bonificaciones;
                
                // Deducciones
                $saludEmpleado = $salario * 0.04;
                $pensionEmpleado = $salario * 0.04;
                
                // Fondo de solidaridad y fondo de ahorro (si salario es alto)
                $fondoSolidaridad = ($salario >= 4000000) ? $salario * 0.01 : 0;
                
                // Retención en la fuente (si salario es muy alto)
                $retencionFuente = ($salario >= 5500000) ? round(($salario - 1300000) * 0.05, 0) : 0;
                
                // Descuentos por créditos o embargo (muy ocasionales)
                $otrosDeducciones = 0;
                if (rand(1, 100) <= 5) { // 5% probabilidad
                    $otrosDeducciones = rand(50000, 300000);
                }
                
                $totalDeducciones = $saludEmpleado + $pensionEmpleado + $fondoSolidaridad + 
                                   $retencionFuente + $otrosDeducciones;
                
                // Neto
                $totalNeto = $totalDevengado - $totalDeducciones;
                
                // Aportes del empleador
                $saludEmpleador = $salario * 0.085;
                $pensionEmpleador = $salario * 0.12;
                $arlEmpleador = $salario * 0.00522;
                $cajaCompensacion = $salario * 0.04;
                $icbf = $salario * 0.03;
                $sena = $salario * 0.02;
                
                // Provisiones
                $cesantias = $salario * 0.0833;
                $interesesCesantias = $cesantias * 0.12;
                $primaServicios = $salario * 0.0833;
                $vacaciones = $salario * 0.0417;
                
                // Costo total empleador
                $costoTotalEmpleador = $salario + $saludEmpleador + $pensionEmpleador + 
                                      $arlEmpleador + $cajaCompensacion + $icbf + $sena +
                                      $cesantias + $interesesCesantias + $primaServicios + $vacaciones;
                
                // Días trabajados (ocasionalmente alguien con incapacidad)
                $diasTrabajados = 30;
                $diasIncapacidad = 0;
                if (rand(1, 100) <= 5) { // 5% probabilidad
                    $diasIncapacidad = rand(1, 5);
                    $diasTrabajados = 30 - $diasIncapacidad;
                }
                
                $detalles[] = [
                    'nomina_id' => $nomina->id,
                    'empleado_id' => $emp['id'],
                    'salario_basico' => $salario,
                    'dias_trabajados' => $diasTrabajados,
                    'dias_incapacidad' => $diasIncapacidad,
                    'dias_licencia' => 0,
                    'dias_suspension' => 0,
                    
                    // INGRESOS
                    'total_devengado' => round($totalDevengado, 2),
                    'auxilio_transporte' => round($auxilioTransporte, 2),
                    'horas_extras' => round($horasExtrasDiurnas + $horasExtrasNocturnas, 2),
                    'recargos' => round($recargos, 2),
                    'comisiones' => round($comisiones, 2),
                    'bonificaciones' => round($bonificaciones, 2),
                    'otros_ingresos' => 0,
                    
                    // BASE DE COTIZACIÓN
                    'base_seguridad_social' => round($salario, 2),
                    'base_parafiscales' => round($salario, 2),
                    
                    // APORTES EMPLEADO
                    'aporte_salud_empleado' => round($saludEmpleado, 2),
                    'aporte_pension_empleado' => round($pensionEmpleado, 2),
                    'fondo_solidaridad_empleado' => round($fondoSolidaridad, 2),
                    
                    // APORTES EMPLEADOR
                    'aporte_salud_empleador' => round($saludEmpleador, 2),
                    'aporte_pension_empleador' => round($pensionEmpleador, 2),
                    'aporte_arl_empleador' => round($arlEmpleador, 2),
                    
                    // PARAFISCALES
                    'aporte_sena' => round($sena, 2),
                    'aporte_icbf' => round($icbf, 2),
                    'aporte_caja' => round($cajaCompensacion, 2),
                    
                    // PROVISIONES
                    'provision_cesantias' => round($cesantias, 2),
                    'provision_intereses_cesantias' => round($interesesCesantias, 2),
                    'provision_prima' => round($primaServicios, 2),
                    'provision_vacaciones' => round($vacaciones, 2),
                    
                    // DEDUCCIONES
                    'total_deducciones' => round($totalDeducciones, 2),
                    'retencion_fuente' => round($retencionFuente, 2),
                    'prestamos' => 0,
                    'embargos' => 0,
                    'otros_descuentos' => round($otrosDeducciones, 2),
                    
                    // TOTALES
                    'total_neto' => round($totalNeto, 2),
                    'costo_total_empleador' => round($costoTotalEmpleador, 2),
                    
                    'observaciones' => $diasIncapacidad > 0 ? "Incapacidad por {$diasIncapacidad} días" : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                $count++;
            }
        }
        
        DB::table('nomina_detalles')->insert($detalles);
        
        $this->command->info("✓ $count detalles de nómina creados (50 empleados × 6 nóminas)");
    }
}