<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\Provision;
use App\Modules\Nomina\Models\PeriodoNomina;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ProvisionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Estructura real del ENUM: ['mensual', 'anual', 'retiro']
     * Todos los valores de provisiones van en UN SOLO registro por período
     */
    public function run(): void
    {
        // Verificar que la tabla existe
        if (!Schema::hasTable('provisiones')) {
            $this->command->error('❌ La tabla provisiones no existe');
            return;
        }
        
        $this->command->info('🔄 Estructura detectada: tipo_provision ENUM (mensual, anual, retiro)');
        $this->command->info('📝 Generando UN registro por empleado con todos los valores de provisiones');
        
        // Obtener período actual
        $periodoActual = PeriodoNomina::where('estado', 'abierto')
            ->orWhere('fecha_fin', '>=', now())
            ->orderBy('fecha_inicio', 'desc')
            ->first();
        
        if (!$periodoActual) {
            // Crear un período por defecto
            $periodoActual = PeriodoNomina::create([
                'codigo' => now()->format('Ym'),
                'nombre' => now()->locale('es')->isoFormat('MMMM YYYY'),
                'fecha_inicio' => now()->startOfMonth(),
                'fecha_fin' => now()->endOfMonth(),
                'estado' => 'abierto',
                'activo' => true,
            ]);
            $this->command->info('📅 Período creado: ' . $periodoActual->nombre);
        }
        
        // Obtener todos los empleados activos
        $empleados = Empleado::where('estado', 'activo')->get();
        
        if ($empleados->isEmpty()) {
            $this->command->warn('⚠️ No hay empleados activos para generar provisiones');
            return;
        }
        
        $this->command->info('👥 Generando provisiones para ' . $empleados->count() . ' empleados...');
        
        foreach ($empleados as $empleado) {
            try {
                // Calcular antigüedad
                $mesesTrabajados = $empleado->fecha_ingreso->diffInMonths(now());
                $salarioBasico = $empleado->salario_basico;
                $diasCausados = 30; // Días del mes
                
                // Fecha de causación
                $fechaCausacion = now()->startOfMonth();
                
                // === CÁLCULOS DE PROVISIONES ===
                
                // 1. CESANTÍAS (8.33% anual)
                $cesantiasMensual = $salarioBasico * 0.0833;
                $saldoCesantias = $cesantiasMensual * $mesesTrabajados;
                
                // 2. INTERESES SOBRE CESANTÍAS (12% anual sobre cesantías)
                $interesesAnual = $saldoCesantias * 0.12;
                $interesesMensual = $interesesAnual / 12;
                $saldoIntereses = $interesesMensual * $mesesTrabajados;
                
                // 3. PRIMA DE SERVICIOS (8.33% anual)
                $primaMensual = $salarioBasico * 0.0833;
                $saldoPrima = $primaMensual * $mesesTrabajados;
                
                // 4. VACACIONES (4.17% anual)
                $vacacionesMensual = $salarioBasico * 0.0417;
                $saldoVacaciones = $vacacionesMensual * $mesesTrabajados;
                $diasVacaciones = floor($mesesTrabajados * 1.25); // 15 días por año
                
                // === CREAR UN SOLO REGISTRO CON TODOS LOS VALORES ===
                Provision::create([
                    'empleado_id' => $empleado->id,
                    'periodo_nomina_id' => $periodoActual->id,
                    'tipo_provision' => 'mensual', // ✅ Valor correcto del ENUM
                    'fecha_causacion' => $fechaCausacion,
                    
                    // SALDOS ACUMULADOS
                    'saldo_cesantias' => round($saldoCesantias, 2),
                    'saldo_intereses' => round($saldoIntereses, 2),
                    'saldo_prima' => round($saldoPrima, 2),
                    'saldo_vacaciones' => round($saldoVacaciones, 2),
                    
                    // VALORES CAUSADOS EN EL MES
                    'valor_causado_cesantias' => round($cesantiasMensual, 2),
                    'valor_causado_intereses' => round($interesesMensual, 2),
                    'valor_causado_prima' => round($primaMensual, 2),
                    'valor_causado_vacaciones' => round($vacacionesMensual, 2),
                    
                    // VALORES PAGADOS (inician en 0)
                    'valor_pagado_cesantias' => 0,
                    'valor_pagado_intereses' => 0,
                    'valor_pagado_prima' => 0,
                    'valor_pagado_vacaciones' => 0,
                    
                    // DATOS DE CÁLCULO
                    'salario_base_calculo' => $salarioBasico,
                    'dias_causados' => $diasCausados,
                    
                    'observaciones' => "Provisión mensual. Antigüedad: {$mesesTrabajados} meses. Días vacaciones: {$diasVacaciones}",
                ]);
                
                $totalProvisiones = $saldoCesantias + $saldoIntereses + $saldoPrima + $saldoVacaciones;
                
                $this->command->info(sprintf(
                    '  ✅ %-35s Total: $%12s',
                    $empleado->nombre_completo,
                    number_format($totalProvisiones, 0)
                ));
                $this->command->line(sprintf(
                    '     Cesantías: $%10s | Intereses: $%10s | Prima: $%10s | Vacaciones: $%10s',
                    number_format($saldoCesantias, 0),
                    number_format($saldoIntereses, 0),
                    number_format($saldoPrima, 0),
                    number_format($saldoVacaciones, 0)
                ));
                
            } catch (\Exception $e) {
                $this->command->error('  ❌ Error con ' . $empleado->nombre_completo . ': ' . $e->getMessage());
            }
        }
        
        $totalRegistros = Provision::count();
        $this->command->info("✅ Total de registros creados: {$totalRegistros}");
        $this->command->info('📊 Estructura: 1 registro por empleado con todos los valores de provisiones');
    }
}