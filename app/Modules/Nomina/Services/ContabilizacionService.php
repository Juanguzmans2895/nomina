<?php

namespace App\Modules\Nomina\Services;

use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Models\AsientoContableNomina;
use App\Modules\Nomina\Models\DetalleAsientoNomina;
use App\Modules\Nomina\Models\CuentaContable;
use Illuminate\Support\Facades\DB;

class ContabilizacionService
{
    /**
     * Generar asiento contable de causación de nómina
     */
    public function generarAsientoCausacion(Nomina $nomina): array
    {
        DB::beginTransaction();
        
        try {
            $numeroAsiento = $this->generarNumeroAsiento('CAN', $nomina->id);
            
            $asiento = AsientoContableNomina::create([
                'numero_asiento' => $numeroAsiento,
                'fecha_asiento' => $nomina->fecha_fin,
                'periodo_contable' => $nomina->fecha_fin->format('Y-m'),
                'descripcion' => "Causación de nómina {$nomina->numero_nomina} - {$nomina->nombre}",
                'nomina_id' => $nomina->id,
                'tipo_asiento' => 'causacion_nomina',
                'estado' => 'borrador',
            ]);
            
            $orden = 1;
            
            // DÉBITOS - Gastos
            
            // 1. Gasto por Sueldos y Salarios
            $this->crearDetalle($asiento, [
                'codigo_cuenta' => '510506', // Sueldos
                'nombre_cuenta' => 'Sueldos y Salarios',
                'debito' => $nomina->total_devengado - $nomina->total_salud_empleado - 
                            $nomina->total_pension_empleado - $nomina->total_fsp_empleado,
                'descripcion' => 'Sueldos causados del período',
                'orden' => $orden++,
            ]);
            
            // 2. Aportes del Empleador - Salud
            $this->crearDetalle($asiento, [
                'codigo_cuenta' => '510527', // Aportes EPS
                'nombre_cuenta' => 'Aportes a Salud',
                'debito' => $nomina->total_salud_empleador,
                'descripcion' => 'Aportes a salud del empleador',
                'orden' => $orden++,
            ]);
            
            // 3. Aportes del Empleador - Pensión
            $this->crearDetalle($asiento, [
                'codigo_cuenta' => '510530', // Aportes Pensión
                'nombre_cuenta' => 'Aportes a Pensión',
                'debito' => $nomina->total_pension_empleador,
                'descripcion' => 'Aportes a pensión del empleador',
                'orden' => $orden++,
            ]);
            
            // 4. ARL
            $this->crearDetalle($asiento, [
                'codigo_cuenta' => '510568', // ARL
                'nombre_cuenta' => 'Riesgos Laborales',
                'debito' => $nomina->total_arl_empleador,
                'descripcion' => 'Aportes a ARL',
                'orden' => $orden++,
            ]);
            
            // 5. Parafiscales - SENA
            if ($nomina->total_sena > 0) {
                $this->crearDetalle($asiento, [
                    'codigo_cuenta' => '510572', // SENA
                    'nombre_cuenta' => 'Aportes SENA',
                    'debito' => $nomina->total_sena,
                    'descripcion' => 'Aportes parafiscales SENA',
                    'orden' => $orden++,
                ]);
            }
            
            // 6. Parafiscales - ICBF
            if ($nomina->total_icbf > 0) {
                $this->crearDetalle($asiento, [
                    'codigo_cuenta' => '510575', // ICBF
                    'nombre_cuenta' => 'Aportes ICBF',
                    'debito' => $nomina->total_icbf,
                    'descripcion' => 'Aportes parafiscales ICBF',
                    'orden' => $orden++,
                ]);
            }
            
            // 7. Caja de Compensación
            if ($nomina->total_caja > 0) {
                $this->crearDetalle($asiento, [
                    'codigo_cuenta' => '510578', // Caja
                    'nombre_cuenta' => 'Caja de Compensación',
                    'debito' => $nomina->total_caja,
                    'descripcion' => 'Aportes a Caja de Compensación',
                    'orden' => $orden++,
                ]);
            }
            
            // 8. Provisiones - Cesantías
            $this->crearDetalle($asiento, [
                'codigo_cuenta' => '510527', // Cesantías
                'nombre_cuenta' => 'Cesantías',
                'debito' => $nomina->total_cesantias,
                'descripcion' => 'Provisión de cesantías',
                'orden' => $orden++,
            ]);
            
            // 9. Provisiones - Intereses sobre Cesantías
            $this->crearDetalle($asiento, [
                'codigo_cuenta' => '510536', // Intereses
                'nombre_cuenta' => 'Intereses sobre Cesantías',
                'debito' => $nomina->total_intereses_cesantias,
                'descripcion' => 'Provisión de intereses sobre cesantías',
                'orden' => $orden++,
            ]);
            
            // 10. Provisiones - Prima de Servicios
            $this->crearDetalle($asiento, [
                'codigo_cuenta' => '510539', // Prima
                'nombre_cuenta' => 'Prima de Servicios',
                'debito' => $nomina->total_prima,
                'descripcion' => 'Provisión de prima de servicios',
                'orden' => $orden++,
            ]);
            
            // 11. Provisiones - Vacaciones
            $this->crearDetalle($asiento, [
                'codigo_cuenta' => '510545', // Vacaciones
                'nombre_cuenta' => 'Vacaciones',
                'debito' => $nomina->total_vacaciones,
                'descripcion' => 'Provisión de vacaciones',
                'orden' => $orden++,
            ]);
            
            // CRÉDITOS - Pasivos
            
            // 12. Salarios por Pagar
            $this->crearDetalle($asiento, [
                'codigo_cuenta' => '233505', // Salarios por pagar
                'nombre_cuenta' => 'Salarios por Pagar',
                'credito' => $nomina->total_neto,
                'descripcion' => 'Salarios netos por pagar',
                'orden' => $orden++,
            ]);
            
            // 13. Aportes por Pagar - Salud
            $totalSalud = $nomina->total_salud_empleado + $nomina->total_salud_empleador;
            $this->crearDetalle($asiento, [
                'codigo_cuenta' => '237005', // Aportes EPS
                'nombre_cuenta' => 'Aportes a EPS por Pagar',
                'credito' => $totalSalud,
                'descripcion' => 'Aportes a salud por pagar',
                'orden' => $orden++,
            ]);
            
            // 14. Aportes por Pagar - Pensión
            $totalPension = $nomina->total_pension_empleado + $nomina->total_pension_empleador + 
                           $nomina->total_fsp_empleado;
            $this->crearDetalle($asiento, [
                'codigo_cuenta' => '237010', // Aportes Pensión
                'nombre_cuenta' => 'Aportes a Pensión por Pagar',
                'credito' => $totalPension,
                'descripcion' => 'Aportes a pensión por pagar',
                'orden' => $orden++,
            ]);
            
            // 15. ARL por Pagar
            $this->crearDetalle($asiento, [
                'codigo_cuenta' => '237015', // ARL
                'nombre_cuenta' => 'ARL por Pagar',
                'credito' => $nomina->total_arl_empleador,
                'descripcion' => 'ARL por pagar',
                'orden' => $orden++,
            ]);
            
            // 16. Parafiscales por Pagar
            if ($nomina->total_sena + $nomina->total_icbf + $nomina->total_caja > 0) {
                $this->crearDetalle($asiento, [
                    'codigo_cuenta' => '237020', // Parafiscales
                    'nombre_cuenta' => 'Aportes Parafiscales por Pagar',
                    'credito' => $nomina->total_sena + $nomina->total_icbf + $nomina->total_caja,
                    'descripcion' => 'Parafiscales por pagar',
                    'orden' => $orden++,
                ]);
            }
            
            // 17. Retención en la Fuente por Pagar
            if ($nomina->total_retencion_fuente > 0) {
                $this->crearDetalle($asiento, [
                    'codigo_cuenta' => '236505', // Retención en la fuente
                    'nombre_cuenta' => 'Retención en la Fuente por Pagar',
                    'credito' => $nomina->total_retencion_fuente,
                    'descripcion' => 'Retención en la fuente por pagar',
                    'orden' => $orden++,
                ]);
            }
            
            // 18. Provisiones por Pagar
            $totalProvisiones = $nomina->total_cesantias + $nomina->total_intereses_cesantias + 
                               $nomina->total_prima + $nomina->total_vacaciones;
            $this->crearDetalle($asiento, [
                'codigo_cuenta' => '261005', // Cesantías consolidadas
                'nombre_cuenta' => 'Provisiones Laborales',
                'credito' => $totalProvisiones,
                'descripcion' => 'Provisiones laborales causadas',
                'orden' => $orden++,
            ]);
            
            // Calcular totales
            $asiento->calcularTotales();
            
            DB::commit();
            
            return [
                'success' => true,
                'asiento_id' => $asiento->id,
                'numero_asiento' => $numeroAsiento,
                'total_debito' => $asiento->total_debito,
                'total_credito' => $asiento->total_credito,
                'cuadrado' => $asiento->cuadrado,
                'diferencia' => $asiento->diferencia,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Generar asiento de pago de nómina
     */
    public function generarAsientoPago(Nomina $nomina, string $numeroCuenta = null): array
    {
        DB::beginTransaction();
        
        try {
            $numeroAsiento = $this->generarNumeroAsiento('PAN', $nomina->id);
            
            $asiento = AsientoContableNomina::create([
                'numero_asiento' => $numeroAsiento,
                'fecha_asiento' => $nomina->fecha_pago_real ?? now(),
                'periodo_contable' => now()->format('Y-m'),
                'descripcion' => "Pago de nómina {$nomina->numero_nomina}",
                'nomina_id' => $nomina->id,
                'tipo_asiento' => 'pago_nomina',
                'estado' => 'borrador',
            ]);
            
            // DÉBITO - Salarios por Pagar
            $this->crearDetalle($asiento, [
                'codigo_cuenta' => '233505',
                'nombre_cuenta' => 'Salarios por Pagar',
                'debito' => $nomina->total_neto,
                'descripcion' => 'Pago de salarios',
                'orden' => 1,
            ]);
            
            // CRÉDITO - Bancos
            $this->crearDetalle($asiento, [
                'codigo_cuenta' => $numeroCuenta ?? '111005',
                'nombre_cuenta' => 'Bancos',
                'credito' => $nomina->total_neto,
                'descripcion' => 'Pago mediante transferencia bancaria',
                'orden' => 2,
            ]);
            
            $asiento->calcularTotales();
            
            DB::commit();
            
            return [
                'success' => true,
                'asiento_id' => $asiento->id,
                'numero_asiento' => $numeroAsiento,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Crear detalle de asiento
     */
    protected function crearDetalle(AsientoContableNomina $asiento, array $datos): void
    {
        // Obtener cuenta contable
        $cuenta = CuentaContable::where('codigo', $datos['codigo_cuenta'])->first();
        
        DetalleAsientoNomina::create([
            'asiento_id' => $asiento->id,
            'cuenta_contable_id' => $cuenta?->id,
            'codigo_cuenta' => $datos['codigo_cuenta'],
            'nombre_cuenta' => $datos['nombre_cuenta'],
            'debito' => $datos['debito'] ?? 0,
            'credito' => $datos['credito'] ?? 0,
            'descripcion' => $datos['descripcion'] ?? null,
            'orden' => $datos['orden'],
        ]);
    }
    
    /**
     * Generar número de asiento automático
     */
    protected function generarNumeroAsiento(string $prefijo, int $nominaId): string
    {
        $anio = now()->year;
        $mes = now()->format('m');
        
        $consecutivo = AsientoContableNomina::where('numero_asiento', 'like', "{$prefijo}-{$anio}-{$mes}-%")
            ->count() + 1;
        
        return sprintf('%s-%d-%s-%04d', $prefijo, $anio, $mes, $consecutivo);
    }
}