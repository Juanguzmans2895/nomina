<?php

namespace App\Modules\Nomina\Services;

use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Models\ProvisionEmpleado;
use App\Modules\Nomina\Models\MovimientoProvision;
use App\Modules\Nomina\Services\Calculo\CalculoProvisionesService;
use Illuminate\Support\Facades\DB;

class ProvisionesService
{
    protected $calculoProvisiones;
    
    public function __construct()
    {
        $this->calculoProvisiones = new CalculoProvisionesService();
    }
    
    /**
     * Causar provisiones para una nómina
     */
    public function causarProvisionesNomina(Nomina $nomina): array
    {
        DB::beginTransaction();
        
        try {
            $resultados = [];
            $detalles = $nomina->detalles()->with('empleado')->get();
            
            foreach ($detalles as $detalle) {
                $empleado = $detalle->empleado;
                $anio = $nomina->fecha_inicio->year;
                
                // Obtener o crear provisión del empleado para este año
                $provision = ProvisionEmpleado::firstOrCreate(
                    [
                        'empleado_id' => $empleado->id,
                        'anio' => $anio,
                    ],
                    [
                        'saldo_inicial_cesantias' => 0,
                        'saldo_inicial_intereses' => 0,
                        'saldo_inicial_prima' => 0,
                        'saldo_inicial_vacaciones' => 0,
                    ]
                );
                
                // Registrar causaciones
                $provision->causacion_cesantias += $detalle->provision_cesantias;
                $provision->causacion_intereses += $detalle->provision_intereses_cesantias;
                $provision->causacion_prima += $detalle->provision_prima;
                $provision->causacion_vacaciones += $detalle->provision_vacaciones;
                
                // Actualizar saldos
                $provision->saldo_cesantias += $detalle->provision_cesantias;
                $provision->saldo_intereses += $detalle->provision_intereses_cesantias;
                $provision->saldo_prima += $detalle->provision_prima;
                $provision->saldo_vacaciones += $detalle->provision_vacaciones;
                
                // Actualizar días y última actualización
                $provision->dias_trabajados_anio += $detalle->dias_trabajados;
                $provision->fecha_ultima_actualizacion = now();
                $provision->nomina_id_ultima_actualizacion = $nomina->id;
                
                $provision->save();
                
                // Registrar movimientos
                $this->registrarMovimiento($empleado->id, 'cesantias', 'causacion', $detalle->provision_cesantias, $nomina);
                $this->registrarMovimiento($empleado->id, 'intereses_cesantias', 'causacion', $detalle->provision_intereses_cesantias, $nomina);
                $this->registrarMovimiento($empleado->id, 'prima', 'causacion', $detalle->provision_prima, $nomina);
                $this->registrarMovimiento($empleado->id, 'vacaciones', 'causacion', $detalle->provision_vacaciones, $nomina);
                
                $resultados[] = [
                    'empleado_id' => $empleado->id,
                    'nombre' => $empleado->nombre_completo,
                    'cesantias' => $detalle->provision_cesantias,
                    'intereses' => $detalle->provision_intereses_cesantias,
                    'prima' => $detalle->provision_prima,
                    'vacaciones' => $detalle->provision_vacaciones,
                    'total' => $detalle->provision_cesantias + 
                              $detalle->provision_intereses_cesantias + 
                              $detalle->provision_prima + 
                              $detalle->provision_vacaciones,
                ];
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'empleados_procesados' => count($resultados),
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
     * Pagar provisión (prima, cesantías, etc.)
     */
    public function pagarProvision(
        Empleado $empleado, 
        string $tipoProvision, 
        float $valor,
        string $numeroDocumento,
        string $descripcion
    ): array {
        DB::beginTransaction();
        
        try {
            $anio = now()->year;
            
            $provision = ProvisionEmpleado::where('empleado_id', $empleado->id)
                ->where('anio', $anio)
                ->first();
            
            if (!$provision) {
                throw new \Exception("No se encontró provisión para el empleado en el año {$anio}");
            }
            
            // Verificar que hay saldo disponible
            $saldoDisponible = $this->getSaldoProvision($provision, $tipoProvision);
            
            if ($valor > $saldoDisponible) {
                throw new \Exception("El valor a pagar ({$valor}) supera el saldo disponible ({$saldoDisponible})");
            }
            
            // Registrar el pago
            switch ($tipoProvision) {
                case 'cesantias':
                    $provision->pago_cesantias += $valor;
                    $provision->saldo_cesantias -= $valor;
                    break;
                case 'intereses_cesantias':
                    $provision->pago_intereses += $valor;
                    $provision->saldo_intereses -= $valor;
                    break;
                case 'prima':
                    $provision->pago_prima += $valor;
                    $provision->saldo_prima -= $valor;
                    break;
                case 'vacaciones':
                    $provision->pago_vacaciones += $valor;
                    $provision->saldo_vacaciones -= $valor;
                    break;
            }
            
            $provision->save();
            
            // Registrar movimiento
            MovimientoProvision::create([
                'empleado_id' => $empleado->id,
                'tipo_provision' => $tipoProvision,
                'tipo_movimiento' => 'pago',
                'valor' => $valor,
                'fecha_movimiento' => now(),
                'numero_documento' => $numeroDocumento,
                'descripcion' => $descripcion,
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'saldo_anterior' => $saldoDisponible,
                'valor_pagado' => $valor,
                'saldo_nuevo' => $saldoDisponible - $valor,
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
     * Liquidar provisiones al terminar el contrato
     */
    public function liquidarProvisiones(
        Empleado $empleado,
        int $diasTrabajados,
        ?float $saldoCesantiasAnterior = null
    ): array {
        $salarioBase = $empleado->salario_basico;
        
        // Calcular liquidación completa
        $liquidacion = $this->calculoProvisiones->calcularLiquidacion(
            $salarioBase,
            $diasTrabajados,
            $saldoCesantiasAnterior ?? 0
        );
        
        return $liquidacion;
    }
    
    /**
     * Obtener saldo de una provisión específica
     */
    protected function getSaldoProvision(ProvisionEmpleado $provision, string $tipo): float
    {
        return match($tipo) {
            'cesantias' => $provision->saldo_cesantias,
            'intereses_cesantias' => $provision->saldo_intereses,
            'prima' => $provision->saldo_prima,
            'vacaciones' => $provision->saldo_vacaciones,
            default => 0,
        };
    }
    
    /**
     * Registrar movimiento de provisión
     */
    protected function registrarMovimiento(
        int $empleadoId,
        string $tipoProvision,
        string $tipoMovimiento,
        float $valor,
        ?Nomina $nomina = null
    ): void {
        if ($valor <= 0) return;
        
        MovimientoProvision::create([
            'empleado_id' => $empleadoId,
            'tipo_provision' => $tipoProvision,
            'tipo_movimiento' => $tipoMovimiento,
            'valor' => $valor,
            'fecha_movimiento' => now(),
            'nomina_id' => $nomina?->id,
            'descripcion' => $tipoMovimiento === 'causacion' 
                ? "Causación de {$tipoProvision} - Nómina {$nomina?->numero_nomina}"
                : "Pago de {$tipoProvision}",
        ]);
    }
    
    /**
     * Obtener reporte de provisiones por empleado
     */
    public function getReporteEmpleado(int $empleadoId, int $anio): array
    {
        $provision = ProvisionEmpleado::where('empleado_id', $empleadoId)
            ->where('anio', $anio)
            ->first();
        
        if (!$provision) {
            return [
                'existe' => false,
                'mensaje' => 'No hay provisiones registradas para este empleado en el año ' . $anio,
            ];
        }
        
        return [
            'existe' => true,
            'saldos' => [
                'cesantias' => $provision->saldo_cesantias,
                'intereses' => $provision->saldo_intereses,
                'prima' => $provision->saldo_prima,
                'vacaciones' => $provision->saldo_vacaciones,
                'total' => $provision->saldo_cesantias + 
                          $provision->saldo_intereses + 
                          $provision->saldo_prima + 
                          $provision->saldo_vacaciones,
            ],
            'causaciones' => [
                'cesantias' => $provision->causacion_cesantias,
                'intereses' => $provision->causacion_intereses,
                'prima' => $provision->causacion_prima,
                'vacaciones' => $provision->causacion_vacaciones,
            ],
            'pagos' => [
                'cesantias' => $provision->pago_cesantias,
                'intereses' => $provision->pago_intereses,
                'prima' => $provision->pago_prima,
                'vacaciones' => $provision->pago_vacaciones,
            ],
            'dias_trabajados' => $provision->dias_trabajados_anio,
        ];
    }
    
    /**
     * Obtener consolidado de provisiones
     */
    public function getConsolidado(int $anio): array
    {
        $provisiones = ProvisionEmpleado::where('anio', $anio)->get();
        
        return [
            'total_empleados' => $provisiones->count(),
            'totales' => [
                'cesantias' => $provisiones->sum('saldo_cesantias'),
                'intereses' => $provisiones->sum('saldo_intereses'),
                'prima' => $provisiones->sum('saldo_prima'),
                'vacaciones' => $provisiones->sum('saldo_vacaciones'),
                'total' => $provisiones->sum('saldo_cesantias') + 
                          $provisiones->sum('saldo_intereses') + 
                          $provisiones->sum('saldo_prima') + 
                          $provisiones->sum('saldo_vacaciones'),
            ],
        ];
    }
}