<?php

namespace App\Services;

use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\DetalleNomina;
use App\Modules\Nomina\Models\NovedadNomina;
use App\Modules\Nomina\Models\ConceptoNomina;
use App\Modules\Nomina\Models\PeriodoNomina;
use Illuminate\Support\Facades\DB;

class NominaCalculoService
{
    protected $empleado;
    protected $periodo;
    protected $nomina;
    protected $detalle;
    protected $salarioBase;
    protected $diasLaborados;
    
    // Constantes de cálculo 2026
    const DIAS_MES = 30;
    const HORAS_MES = 240;
    const AUXILIO_TRANSPORTE_2026 = 249095;
    const SALARIO_MINIMO_2026 = 3501810;
    
    // Porcentajes fijos 2026
    const AFP_EMPLEADO = 0.04;        // 4%
    const AFP_EMPLEADOR = 0.12;       // 12%
    const SALUD_EMPLEADO = 0.04;      // 4%
    const SALUD_EMPLEADOR = 0.085;    // 8.5%
    const ARL = 0.052;                // 5.2% (promedio)
    const CAJA_COMPENSACION = 0.04;   // 4%
    const SENA = 0.02;                // 2%
    const ICBF = 0.03;                // 3%
    
    /**
     * Constructor
     */
    public function __construct()
    {
    }
    
    /**
     * Calcular nómina completa para un empleado en un período
     */
    public function calcularNomina(Empleado $empleado, PeriodoNomina $periodo, Nomina $nomina)
    {
        $this->empleado = $empleado;
        $this->periodo = $periodo;
        $this->nomina = $nomina;
        $this->salarioBase = $empleado->salario_basico;
        
        // Obtener o crear detalle
        $this->detalle = DetalleNomina::firstOrCreate(
            ['nomina_id' => $nomina->id, 'empleado_id' => $empleado->id],
            ['salario_basico' => $this->salarioBase]
        );
        
        // === PASO 1: CALCULAR DEVENGOS ===
        $this->calcularDevengos();
        
        // === PASO 2: CALCULAR DEDUCCIONES ===
        $this->calcularDeducciones();
        
        // === PASO 3: CALCULAR APORTES EMPLEADOR ===
        $this->calcularAportesEmpleador();
        
        // === PASO 4: CALCULAR PROVISIONES ===
        $this->calcularProvisiones();
        
        // === PASO 5: CALCULAR TOTALES ===
        $this->calcularTotales();
        
        // Guardar
        $this->detalle->save();
        
        return $this->detalle;
    }
    
    /**
     * FASE 1: CALCULAR DEVENGOS
     */
    protected function calcularDevengos()
    {
        // ═══════════════════════════════════════════════════════════
        // 1. SALARIO BÁSICO
        // Fórmula: salario_plan / 30 * dias_trabajados
        // ═══════════════════════════════════════════════════════════
        $this->detalle->dias_trabajados = $this->diasLaboradosDelPeriodo();
        $this->detalle->salario_basico = round($this->salarioBase / self::DIAS_MES * $this->detalle->dias_trabajados, 2);
        
        $ibc = $this->detalle->salario_basico; // IBC = Ingreso Base de Cotización
        
        // ═══════════════════════════════════════════════════════════
        // 2. AUXILIO DE TRANSPORTE
        // Si salario < SMLV: auxilio_transporte = 249095/30 * días_trabajados
        // Si salario >= SMLV: no tiene derecho
        // ═══════════════════════════════════════════════════════════
        if ($this->salarioBase < self::SALARIO_MINIMO_2026) {
            $this->detalle->auxilio_transporte = round(
                (self::AUXILIO_TRANSPORTE_2026 / self::DIAS_MES) * $this->detalle->dias_trabajados,
                2
            );
        } else {
            $this->detalle->auxilio_transporte = 0;
        }
        
        // ═══════════════════════════════════════════════════════════
        // 3. HORAS EXTRAS Y RECARGOS (OBTENER DEL MODELO NovedadNomina)
        // ═══════════════════════════════════════════════════════════
        $novedades = NovedadNomina::where('empleado_id', $this->empleado->id)
            ->where('periodo_id', $this->periodo->id)
            ->where('estado', 'aprobada')
            ->with('concepto')
            ->get();
        
        // Inicializar campos de recargos
        $horasExtras = $this->calcularRecargosDelPeriodo($novedades);
        
        $this->detalle->horas_extras_diurnas = $horasExtras['diurnas'] ?? 0;
        $this->detalle->horas_extras_nocturnas = $horasExtras['nocturnas'] ?? 0;
        $this->detalle->horas_extras_dominicales = $horasExtras['dominicales'] ?? 0;
        $this->detalle->recargo_nocturno = $horasExtras['recargo_nocturno'] ?? 0;
        $this->detalle->recargo_dominical = $horasExtras['recargo_dominical'] ?? 0;
        $this->detalle->bonificaciones = $horasExtras['bonificaciones'] ?? 0;
        $this->detalle->comisiones = $horasExtras['comisiones'] ?? 0;
        
        // ═══════════════════════════════════════════════════════════
        // Total Devengado = Salario + Auxilio + Horas Extras + Recargos + Otros
        // ═══════════════════════════════════════════════════════════
        $this->detalle->total_devengado = round(
            $this->detalle->salario_basico +
            $this->detalle->auxilio_transporte +
            $this->detalle->horas_extras_diurnas +
            $this->detalle->horas_extras_nocturnas +
            $this->detalle->horas_extras_dominicales +
            $this->detalle->recargo_nocturno +
            $this->detalle->recargo_dominical +
            $this->detalle->bonificaciones +
            $this->detalle->comisiones +
            ($this->detalle->otros_devengados ?? 0),
            2
        );
    }
    
    /**
     * FASE 2: CALCULAR DEDUCCIONES
     */
    protected function calcularDeducciones()
    {
        // IBC para deducciones (sin auxilio de transporte)
        $ibc = $this->detalle->salario_basico;
        
        // ═══════════════════════════════════════════════════════════
        // DEDUCCIONES OBLIGATORIAS DE SEGURIDAD SOCIAL
        // ═══════════════════════════════════════════════════════════
        
        // Salud Empleado (4%)
        $this->detalle->salud_empleado = round($ibc * self::SALUD_EMPLEADO, 2);
        
        // Pensión Empleado (4%)
        $this->detalle->pension_empleado = round($ibc * self::AFP_EMPLEADO, 2);
        
        // Fondo de Solidaridad (1% si salario > 4 SMLV, 1.4% si > 16 SMLV)
        $salariosMinimos = $this->salarioBase / self::SALARIO_MINIMO_2026;
        if ($salariosMinimos > 16) {
            $this->detalle->fondo_solidaridad = round($ibc * 0.014, 2);
        } elseif ($salariosMinimos > 4) {
            $this->detalle->fondo_solidaridad = round($ibc * 0.01, 2);
        } else {
            $this->detalle->fondo_solidaridad = 0;
        }
        
        // ═══════════════════════════════════════════════════════════
        // OTRAS DEDUCCIONES (DE NOVEDADES)
        // ═══════════════════════════════════════════════════════════
        $novedadesDeduccion = NovedadNomina::where('empleado_id', $this->empleado->id)
            ->where('periodo_id', $this->periodo->id)
            ->where('estado', 'aprobada')
            ->whereHas('concepto', function ($q) {
                $q->where('clasificacion', 'deducido');
            })
            ->get();
        
        // Procesar cada deducción
        foreach ($novedadesDeduccion as $novedad) {
            $this->aplicarDeduccion($novedad);
        }
        
        // ═══════════════════════════════════════════════════════════
        // TOTAL DEDUCCIONES
        // ═══════════════════════════════════════════════════════════
        $this->detalle->total_deducciones = round(
            $this->detalle->salud_empleado +
            $this->detalle->pension_empleado +
            $this->detalle->fondo_solidaridad +
            ($this->detalle->retencion_fuente ?? 0) +
            ($this->detalle->sindicato ?? 0) +
            ($this->detalle->creditos ?? 0) +
            ($this->detalle->embargos ?? 0) +
            ($this->detalle->otras_deducciones ?? 0),
            2
        );
    }
    
    /**
     * FASE 3: CALCULAR APORTES DEL EMPLEADOR
     */
    protected function calcularAportesEmpleador()
    {
        $ibc = $this->detalle->salario_basico;
        
        // Salud (8.5%)
        $this->detalle->salud_empleador = round($ibc * self::SALUD_EMPLEADOR, 2);
        
        // Pensión (12%)
        $this->detalle->pension_empleador = round($ibc * self::AFP_EMPLEADOR, 2);
        
        // ARL (5.2% promedio)
        $this->detalle->arl_empleador = round($ibc * self::ARL, 2);
        
        // Caja de Compensación (4%)
        $this->detalle->caja_compensacion = round($ibc * self::CAJA_COMPENSACION, 2);
        
        // ICBF (3%)
        $this->detalle->icbf = round($ibc * self::ICBF, 2);
        
        // SENA (2%)
        $this->detalle->sena = round($ibc * self::SENA, 2);
    }
    
    /**
     * FASE 4: CALCULAR PROVISIONES
     */
    protected function calcularProvisiones()
    {
        // Provisiones = Porcentaje del Total Devengado / 12
        $totalDevengado = $this->detalle->total_devengado;
        
        // Cesantías (1/12 del total devengado)
        $this->detalle->cesantias = round($totalDevengado / 12, 2);
        
        // Intereses Cesantías (12% anual sobre cesantías = 1% mensual)
        $this->detalle->intereses_cesantias = round($this->detalle->cesantias * 0.12, 2);
        
        // Prima de Servicios (1/12 del total devengado)
        $this->detalle->prima_servicios = round($totalDevengado / 12, 2);
        
        // Vacaciones (1/24 del total devengado)
        $this->detalle->vacaciones = round($totalDevengado / 24, 2);
    }
    
    /**
     * FASE 5: CALCULAR TOTALES FINALES
     */
    protected function calcularTotales()
    {
        // TOTAL A PAGAR AL EMPLEADO (NETO)
        $this->detalle->total_neto = round(
            $this->detalle->total_devengado - $this->detalle->total_deducciones,
            2
        );
        
        // COSTO TOTAL PARA EL EMPLEADOR
        $this->detalle->costo_total_empleador = round(
            $this->detalle->total_devengado +
            $this->detalle->salud_empleador +
            $this->detalle->pension_empleador +
            $this->detalle->arl_empleador +
            $this->detalle->caja_compensacion +
            $this->detalle->icbf +
            $this->detalle->sena +
            $this->detalle->cesantias +
            $this->detalle->intereses_cesantias +
            $this->detalle->prima_servicios +
            $this->detalle->vacaciones,
            2
        );
    }
    
    /**
     * Días laborados en el período
     */
    protected function diasLaboradosDelPeriodo(): int
    {
        // Por defecto 30, ajustar según días incapacidad, licencia, etc.
        $dias = 30;
        
        // Restar días de incapacidad
        $dias -= $this->detalle->dias_incapacidad ?? 0;
        
        // Restar días de licencia no remunerada
        $dias -= $this->detalle->dias_licencia ?? 0;
        
        return max(0, $dias);
    }
    
    /**
     * Calcular recargos y horas extras del período
     */
    protected function calcularRecargosDelPeriodo($novedades): array
    {
        $resultado = [
            'diurnas' => 0,
            'nocturnas' => 0,
            'dominicales' => 0,
            'recargo_nocturno' => 0,
            'recargo_dominical' => 0,
            'bonificaciones' => 0,
            'comisiones' => 0,
        ];
        
        foreach ($novedades as $novedad) {
            if (!$novedad->concepto) continue;
            
            $codigo = strtoupper($novedad->concepto->codigo);
            $cantidad = $novedad->cantidad ?? 0;
            $valorUnitario = $novedad->valor_unitario ?? 0;
            
            // Valor por hora
            $valorHora = $this->salarioBase / self::HORAS_MES;
            
            // Mapear a cada tipo
            match ($codigo) {
                'HED' => $resultado['diurnas'] += $novedad->valor_total ?? 0,
                'HEN' => $resultado['nocturnas'] += $novedad->valor_total ?? 0,
                'HEDF', 'HENF' => $resultado['dominicales'] += $novedad->valor_total ?? 0,
                'RN' => $resultado['recargo_nocturno'] += $novedad->valor_total ?? 0,
                'RDF', 'RNDF' => $resultado['recargo_dominical'] += $novedad->valor_total ?? 0,
                'BONO' => $resultado['bonificaciones'] += $novedad->valor_total ?? 0,
                'COMISION' => $resultado['comisiones'] += $novedad->valor_total ?? 0,
                default => null,
            };
        }
        
        return $resultado;
    }
    
    /**
     * Aplicar deducción desde novedad
     */
    protected function aplicarDeduccion(NovedadNomina $novedad)
    {
        $codigo = strtoupper($novedad->concepto->codigo ?? '');
        $monto = $novedad->valor_total ?? 0;
        
        // Aplicar según tipo de deducción
        match ($codigo) {
            'DESC_SINDICATO' => $this->detalle->sindicato = ($this->detalle->sindicato ?? 0) + $monto,
            'DESC_CREDITO' => $this->detalle->creditos = ($this->detalle->creditos ?? 0) + $monto,
            'DESC_EMBARGO' => $this->detalle->embargos = ($this->detalle->embargos ?? 0) + $monto,
            default => $this->detalle->otras_deducciones = ($this->detalle->otras_deducciones ?? 0) + $monto,
        };
    }
}
