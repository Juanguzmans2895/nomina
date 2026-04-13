<?php

namespace App\Modules\Nomina\Services;

use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Models\NominaDetalle;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\ConceptoNomina;
use App\Modules\Nomina\Models\NovedadNomina;
use App\Modules\Nomina\Models\NominaConcepto;
use App\Modules\Nomina\Services\Calculo\CalculoSeguridadSocialService;
use App\Modules\Nomina\Services\Calculo\CalculoParafiscalesService;
use App\Modules\Nomina\Services\Calculo\CalculoRetencionService;
use App\Modules\Nomina\Services\Calculo\CalculoProvisionesService;
use Illuminate\Support\Facades\DB;

class LiquidacionService
{
    protected $calculoSS;
    protected $calculoParafiscales;
    protected $calculoRetencion;
    protected $calculoProvisiones;
    
    public function __construct()
    {
        $this->calculoSS = new CalculoSeguridadSocialService();
        $this->calculoParafiscales = new CalculoParafiscalesService();
        $this->calculoRetencion = new CalculoRetencionService();
        $this->calculoProvisiones = new CalculoProvisionesService();
    }
    
    /**
     * Liquidar una nómina completa
     */
    public function liquidar(Nomina $nomina, array $empleadosIds = []): array
    {
        DB::beginTransaction();
        
        try {
            $empleados = empty($empleadosIds) 
                ? Empleado::activos()->get()
                : Empleado::whereIn('id', $empleadosIds)->get();
            
            $resultados = [];
            
            foreach ($empleados as $empleado) {
                $resultado = $this->liquidarEmpleado($nomina, $empleado);
                $resultados[] = $resultado;
            }
            
            // Calcular totales de la nómina
            $nomina->calcularTotales();
            
            DB::commit();
            
            return [
                'success' => true,
                'empleados_procesados' => count($resultados),
                'total_devengado' => $nomina->total_devengado,
                'total_deducciones' => $nomina->total_deducciones,
                'total_neto' => $nomina->total_neto,
                'detalles' => $resultados,
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
     * Liquidar un empleado individual
     */
    public function liquidarEmpleado(Nomina $nomina, Empleado $empleado): array
    {
        // 1. Obtener datos base
        $salarioBasico = $empleado->salario_basico;
        $diasTrabajados = 30; // Por defecto, se puede ajustar
        
        // 2. Calcular conceptos devengados
        $devengados = $this->calcularDevengados($nomina, $empleado, $diasTrabajados);
        
        // 3. Calcular base de cotización
        $baseSeguridad = $this->calcularBaseSeguridadSocial($devengados);
        $baseParafiscales = $this->calcularBaseParafiscales($devengados);
        
        // 4. Calcular seguridad social
        $seguridadSocial = $this->calculoSS->calcular($baseSeguridad, [
            'clase_riesgo' => $empleado->clase_riesgo
        ]);
        
        // 5. Calcular parafiscales
        $parafiscales = $this->calculoParafiscales->calcular($baseParafiscales);
        
        // 6. Calcular provisiones
        $provisiones = $this->calculoProvisiones->calcularTodas($baseSeguridad);
        
        // 7. Calcular deducciones
        $deducciones = $this->calcularDeducciones($nomina, $empleado, $seguridadSocial, $devengados['total']);
        
        // 8. Calcular totales
        $totalDevengado = $devengados['total'];
        $totalDeducciones = $deducciones['total'];
        $totalNeto = $totalDevengado - $totalDeducciones;
        
        // 9. Crear el detalle de nómina
        $detalle = NominaDetalle::create([
            'nomina_id' => $nomina->id,
            'empleado_id' => $empleado->id,
            'salario_basico' => $salarioBasico,
            'dias_trabajados' => $diasTrabajados,
            
            // Devengados
            'total_devengado' => $totalDevengado,
            'auxilio_transporte' => $devengados['auxilio_transporte'],
            'horas_extras' => $devengados['horas_extras'],
            'recargos' => $devengados['recargos'],
            'comisiones' => $devengados['comisiones'],
            'bonificaciones' => $devengados['bonificaciones'],
            'otros_ingresos' => $devengados['otros_ingresos'],
            
            // Bases
            'base_seguridad_social' => $baseSeguridad,
            'base_parafiscales' => $baseParafiscales,
            
            // Seguridad Social - Empleado
            'aporte_salud_empleado' => $seguridadSocial['aporte_salud_empleado'],
            'aporte_pension_empleado' => $seguridadSocial['aporte_pension_empleado'],
            'fondo_solidaridad_empleado' => $seguridadSocial['fondo_solidaridad_empleado'],
            
            // Seguridad Social - Empleador
            'aporte_salud_empleador' => $seguridadSocial['aporte_salud_empleador'],
            'aporte_pension_empleador' => $seguridadSocial['aporte_pension_empleador'],
            'aporte_arl_empleador' => $seguridadSocial['aporte_arl_empleador'],
            
            // Parafiscales
            'aporte_sena' => $parafiscales['aporte_sena'],
            'aporte_icbf' => $parafiscales['aporte_icbf'],
            'aporte_caja' => $parafiscales['aporte_caja'],
            
            // Provisiones
            'provision_cesantias' => $provisiones['cesantias'],
            'provision_intereses_cesantias' => $provisiones['intereses_cesantias'],
            'provision_prima' => $provisiones['prima'],
            'provision_vacaciones' => $provisiones['vacaciones'],
            
            // Deducciones
            'total_deducciones' => $totalDeducciones,
            'retencion_fuente' => $deducciones['retencion_fuente'],
            'prestamos' => $deducciones['prestamos'],
            'embargos' => $deducciones['embargos'],
            'otros_descuentos' => $deducciones['otros_descuentos'],
            
            // Totales
            'total_neto' => $totalNeto,
        ]);
        
        // Calcular costo total empleador
        $detalle->costo_total_empleador = $detalle->calcularCostoTotalEmpleador();
        $detalle->save();
        
        // 10. Registrar conceptos aplicados
        $this->registrarConceptos($detalle, $devengados, $deducciones);
        
        return [
            'empleado_id' => $empleado->id,
            'nombre' => $empleado->nombre_completo,
            'total_devengado' => $totalDevengado,
            'total_deducciones' => $totalDeducciones,
            'total_neto' => $totalNeto,
            'costo_empleador' => $detalle->costo_total_empleador,
        ];
    }
    
    /**
     * Calcular conceptos devengados
     */
    protected function calcularDevengados(Nomina $nomina, Empleado $empleado, int $dias): array
    {
        $salarioBasico = $empleado->salario_basico;
        
        // Salario proporcional por días
        $salarioProporcional = ($salarioBasico / 30) * $dias;
        
        // Auxilio de transporte
        $auxilioTransporte = 0;
        if ($empleado->calculaAuxilioTransporte()) {
            $auxilioTransporte = 162000; // Valor 2024, debería estar en config
            // Proporcional por días
            $auxilioTransporte = ($auxilioTransporte / 30) * $dias;
        }
        
        // ══════════════════════════════════════════════════════════
        // CRÍTICO: Buscar NOVEDADES APROBADAS (no pendientes)
        // ══════════════════════════════════════════════════════════
        // Obtener novedades del empleado para este período
        $novedades = NovedadNomina::where('empleado_id', $empleado->id)
            ->where('periodo_id', $nomina->periodo_nomina_id)
            ->where('estado', 'aprobada')  // ← CORRECCIÓN: Debe ser 'aprobada', no 'pendiente'
            ->get();
        
        $horasExtras = 0;
        $recargos = 0;
        $comisiones = 0;
        $bonificaciones = 0;
        $otrosIngresos = 0;
        
        foreach ($novedades as $novedad) {
            $concepto = $novedad->concepto;
            
            if ($concepto->esDevengado()) {
                // Clasificar según tipo de concepto
                switch ($concepto->codigo) {
                    case '010':
                    case '011':
                    case '012':
                        $horasExtras += $novedad->valor_total;
                        break;
                    case '020':
                    case '021':
                        $recargos += $novedad->valor_total;
                        break;
                    case '030':
                        $comisiones += $novedad->valor_total;
                        break;
                    case '031':
                        $bonificaciones += $novedad->valor_total;
                        break;
                    default:
                        $otrosIngresos += $novedad->valor_total;
                }
                
                // Marcar como procesada
                $novedad->procesada = true;
                $novedad->nomina_id = $nomina->id;
                $novedad->estado = 'aplicada';
                $novedad->save();
            }
        }
        
        // Conceptos fijos del empleado
        $conceptosFijos = $empleado->conceptosFijosActivos()
            ->devengados()
            ->get();
        
        foreach ($conceptosFijos as $concepto) {
            $valor = $concepto->pivot->valor ?? 0;
            $porcentaje = $concepto->pivot->porcentaje ?? 0;
            
            if ($porcentaje > 0) {
                $valor = $salarioBasico * ($porcentaje / 100);
            }
            
            $otrosIngresos += $valor;
        }
        
        $total = $salarioProporcional + $auxilioTransporte + $horasExtras + 
                 $recargos + $comisiones + $bonificaciones + $otrosIngresos;
        
        return [
            'salario_basico' => round($salarioProporcional, 2),
            'auxilio_transporte' => round($auxilioTransporte, 2),
            'horas_extras' => round($horasExtras, 2),
            'recargos' => round($recargos, 2),
            'comisiones' => round($comisiones, 2),
            'bonificaciones' => round($bonificaciones, 2),
            'otros_ingresos' => round($otrosIngresos, 2),
            'total' => round($total, 2),
        ];
    }
    
    /**
     * Calcular deducciones
     */
    protected function calcularDeducciones(
        Nomina $nomina, 
        Empleado $empleado, 
        array $seguridadSocial,
        float $totalDevengado
    ): array {
        // Deducciones de seguridad social
        $saludEmpleado = $seguridadSocial['aporte_salud_empleado'];
        $pensionEmpleado = $seguridadSocial['aporte_pension_empleado'];
        $fspEmpleado = $seguridadSocial['fondo_solidaridad_empleado'];
        
        // Retención en la fuente
        $retencion = 0;
        if (!$empleado->exento_retencion) {
            $calculoRetencion = $this->calculoRetencion->calcular($totalDevengado, [
                'incluir_salud' => true,
                'incluir_pension' => true,
            ]);
            $retencion = $calculoRetencion['retencion'];
        }
        
        // Préstamos y embargos
        $prestamos = 0;
        $embargos = 0;
        $otrosDescuentos = 0;
        
        // Obtener novedades de deducciones
        $novedades = NovedadNomina::where('empleado_id', $empleado->id)
            ->where('periodo_id', $nomina->periodo_nomina_id)
            ->where('estado', 'pendiente')
            ->get();
        
        foreach ($novedades as $novedad) {
            $concepto = $novedad->concepto;
            
            if ($concepto->esDeducido()) {
                switch ($concepto->codigo) {
                    case '120':
                        $prestamos += $novedad->valor_total;
                        break;
                    case '121':
                        $embargos += $novedad->valor_total;
                        break;
                    default:
                        $otrosDescuentos += $novedad->valor_total;
                }
                
                $novedad->procesada = true;
                $novedad->nomina_id = $nomina->id;
                $novedad->estado = 'aplicada';
                $novedad->save();
            }
        }
        
        $total = $saludEmpleado + $pensionEmpleado + $fspEmpleado + 
                 $retencion + $prestamos + $embargos + $otrosDescuentos;
        
        return [
            'aporte_salud' => round($saludEmpleado, 2),
            'aporte_pension' => round($pensionEmpleado, 2),
            'fondo_solidaridad' => round($fspEmpleado, 2),
            'retencion_fuente' => round($retencion, 2),
            'prestamos' => round($prestamos, 2),
            'embargos' => round($embargos, 2),
            'otros_descuentos' => round($otrosDescuentos, 2),
            'total' => round($total, 2),
        ];
    }
    
    /**
     * Calcular base de seguridad social
     */
    protected function calcularBaseSeguridadSocial(array $devengados): float
    {
        // Base = Salario + Horas Extras + Recargos + Comisiones
        // No incluye: Auxilio transporte, bonificaciones no constitutivas
        return $devengados['salario_basico'] + 
               $devengados['horas_extras'] + 
               $devengados['recargos'] + 
               $devengados['comisiones'];
    }
    
    /**
     * Calcular base de parafiscales
     */
    protected function calcularBaseParafiscales(array $devengados): float
    {
        // Misma base que seguridad social
        return $this->calcularBaseSeguridadSocial($devengados);
    }
    
    /**
     * Registrar conceptos aplicados
     */
    protected function registrarConceptos(NominaDetalle $detalle, array $devengados, array $deducciones): void
    {
        // Registrar devengados
        if ($devengados['salario_basico'] > 0) {
            $this->crearNominaConcepto($detalle, '001', 'Salario Básico', 'devengado', $devengados['salario_basico']);
        }
        
        if ($devengados['auxilio_transporte'] > 0) {
            $this->crearNominaConcepto($detalle, '002', 'Auxilio de Transporte', 'devengado', $devengados['auxilio_transporte']);
        }
        
        // ... más conceptos según necesidad
        
        // Registrar deducciones
        if ($deducciones['aporte_salud'] > 0) {
            $this->crearNominaConcepto($detalle, '100', 'Aporte Salud', 'deducido', $deducciones['aporte_salud']);
        }
        
        if ($deducciones['aporte_pension'] > 0) {
            $this->crearNominaConcepto($detalle, '101', 'Aporte Pensión', 'deducido', $deducciones['aporte_pension']);
        }
        
        if ($deducciones['retencion_fuente'] > 0) {
            $this->crearNominaConcepto($detalle, '110', 'Retención en la Fuente', 'deducido', $deducciones['retencion_fuente']);
        }
    }
    
    /**
     * Crear un registro de concepto aplicado
     */
    protected function crearNominaConcepto(
        NominaDetalle $detalle, 
        string $codigo, 
        string $nombre, 
        string $clasificacion, 
        float $valor
    ): void {
        NominaConcepto::create([
            'nomina_detalle_id' => $detalle->id,
            'concepto_nomina_id' => ConceptoNomina::where('codigo', $codigo)->first()?->id,
            'codigo_concepto' => $codigo,
            'nombre_concepto' => $nombre,
            'clasificacion' => $clasificacion,
            'valor' => $valor,
        ]);
    }
}