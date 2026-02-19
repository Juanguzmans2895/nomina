<?php

namespace App\Modules\Nomina\Services\Reportes;

use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Models\NominaDetalle;
use App\Modules\Nomina\Models\Empleado;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConsolidadoService
{
    /**
     * Generar PDF consolidado de nómina
     */
    public function generarPDF(Nomina $nomina)
    {
        $nomina->load(['detalles.empleado', 'tipo', 'periodo']);
        
        $data = [
            'nomina' => $nomina,
            'detalles' => $this->procesarDetalles($nomina),
            'totales' => $this->calcularTotales($nomina),
            'resumenConceptos' => $this->obtenerResumenConceptos($nomina),
            'distribucionDependencias' => $this->obtenerDistribucionDependencias($nomina),
        ];
        
        $pdf = \PDF::loadView('nomina.reportes.consolidado', $data);
        $pdf->setPaper('letter', 'portrait');
        
        return $pdf;
    }

    /**
     * Procesar detalles de nómina
     */
    private function procesarDetalles(Nomina $nomina)
    {
        return $nomina->detalles->map(function($detalle) {
            return [
                'empleado' => [
                    'nombre' => $detalle->empleado->nombre_completo,
                    'documento' => $detalle->empleado->numero_documento,
                    'cargo' => $detalle->empleado->cargo,
                ],
                'salario_basico' => $detalle->salario_basico,
                'total_devengado' => $detalle->total_devengado,
                'total_deducciones' => $detalle->total_deducciones,
                'total_neto' => $detalle->total_neto,
                'aporte_salud' => $detalle->aporte_salud,
                'aporte_pension' => $detalle->aporte_pension,
            ];
        });
    }

    /**
     * Calcular totales
     */
    private function calcularTotales(Nomina $nomina)
    {
        return [
            'empleados' => $nomina->detalles->count(),
            'salario_basico' => $nomina->detalles->sum('salario_basico'),
            'total_devengado' => $nomina->detalles->sum('total_devengado'),
            'total_deducciones' => $nomina->detalles->sum('total_deducciones'),
            'total_neto' => $nomina->detalles->sum('total_neto'),
            'total_salud' => $nomina->detalles->sum('aporte_salud'),
            'total_pension' => $nomina->detalles->sum('aporte_pension'),
            'total_seguridad_social' => $nomina->detalles->sum('aporte_salud') + $nomina->detalles->sum('aporte_pension'),
        ];
    }

    /**
     * Obtener resumen de conceptos
     */
    private function obtenerResumenConceptos(Nomina $nomina)
    {
        // Aquí se procesarían los conceptos de cada detalle
        return [
            'devengados' => [],
            'deducciones' => [],
        ];
    }

    /**
     * Obtener distribución por dependencias
     */
    private function obtenerDistribucionDependencias(Nomina $nomina)
    {
        return $nomina->detalles
            ->groupBy(function($detalle) {
                return $detalle->empleado->dependencia ?? 'Sin Dependencia';
            })
            ->map(function($grupo) {
                return [
                    'empleados' => $grupo->count(),
                    'total_neto' => $grupo->sum('total_neto'),
                ];
            });
    }

    /**
     * Obtener datos ejecutivos para reporte
     */
    public function obtenerDatosEjecutivos($periodo)
    {
        $fechaInicio = Carbon::parse($periodo . '-01');
        $fechaFin = $fechaInicio->copy()->endOfMonth();
        
        $nominas = Nomina::whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])->get();
        
        $metricas = [
            'total_nomina' => $nominas->sum('total_neto'),
            'total_empleados' => $nominas->sum('numero_empleados'),
            'total_seguridad_social' => $this->calcularSeguridadSocialTotal($nominas),
            'costo_total_empleador' => $this->calcularCostoTotalEmpleador($nominas),
            'variacion_mes_anterior' => $this->calcularVariacion($periodo),
            'variacion_empleados' => 0,
        ];
        
        return [
            'periodo' => $fechaInicio->locale('es')->isoFormat('MMMM YYYY'),
            'metricas' => $metricas,
            'indicadores' => $this->calcularIndicadores($nominas),
            'porDependencia' => $this->obtenerPorDependencia($nominas),
            'composicionDevengados' => $this->obtenerComposicionDevengados($nominas),
            'composicionDeducciones' => $this->obtenerComposicionDeducciones($nominas),
            'rangosSalariales' => $this->obtenerRangosSalariales($nominas),
            'alertas' => $this->generarAlertas($nominas),
            'totales' => $this->calcularTotalesGenerales($nominas),
        ];
    }

    /**
     * Calcular seguridad social total
     */
    private function calcularSeguridadSocialTotal($nominas)
    {
        $total = 0;
        
        foreach ($nominas as $nomina) {
            $nomina->load('detalles');
            foreach ($nomina->detalles as $detalle) {
                $ibc = $detalle->salario_basico;
                $total += ($ibc * 0.04) + ($ibc * 0.085); // Salud
                $total += ($ibc * 0.04) + ($ibc * 0.12);  // Pensión
                $total += $ibc * 0.00522; // ARL (promedio)
            }
        }
        
        return $total;
    }

    /**
     * Calcular costo total empleador
     */
    private function calcularCostoTotalEmpleador($nominas)
    {
        $total = $nominas->sum('total_neto');
        $seguridadSocial = $this->calcularSeguridadSocialTotal($nominas);
        $parafiscales = $this->calcularParafiscalesTotal($nominas);
        $provisiones = $this->calcularProvisionesTotal($nominas);
        
        return $total + $seguridadSocial + $parafiscales + $provisiones;
    }

    /**
     * Calcular parafiscales total
     */
    private function calcularParafiscalesTotal($nominas)
    {
        $total = 0;
        
        foreach ($nominas as $nomina) {
            $nomina->load('detalles');
            foreach ($nomina->detalles as $detalle) {
                if ($detalle->salario_basico > 10 * 1300000) continue; // No aplica para salarios > 10 SMLV
                
                $ibc = $detalle->salario_basico;
                $total += $ibc * 0.02; // SENA
                $total += $ibc * 0.03; // ICBF
                $total += $ibc * 0.04; // Caja
            }
        }
        
        return $total;
    }

    /**
     * Calcular provisiones total
     */
    private function calcularProvisionesTotal($nominas)
    {
        $total = 0;
        
        foreach ($nominas as $nomina) {
            $nomina->load('detalles');
            foreach ($nomina->detalles as $detalle) {
                $salario = $detalle->salario_basico;
                $total += $salario * 0.0833; // Cesantías
                $total += $salario * 0.01;   // Intereses
                $total += $salario * 0.0833; // Prima
                $total += $salario * 0.0417; // Vacaciones
            }
        }
        
        return $total;
    }

    /**
     * Calcular variación
     */
    private function calcularVariacion($periodo)
    {
        // Implementar lógica de comparación con mes anterior
        return 0;
    }

    /**
     * Calcular indicadores
     */
    private function calcularIndicadores($nominas)
    {
        $totalEmpleados = $nominas->sum('numero_empleados');
        $totalNomina = $nominas->sum('total_neto');
        
        return [
            'salario_promedio' => $totalEmpleados > 0 ? $totalNomina / $totalEmpleados : 0,
            'costo_por_empleado' => $totalEmpleados > 0 ? $this->calcularCostoTotalEmpleador($nominas) / $totalEmpleados : 0,
            'porcentaje_deducciones' => $totalNomina > 0 ? ($nominas->sum('total_deducciones') / $totalNomina) * 100 : 0,
            'porcentaje_seguridad_social' => $totalNomina > 0 ? ($this->calcularSeguridadSocialTotal($nominas) / $totalNomina) * 100 : 0,
        ];
    }

    /**
     * Obtener datos por dependencia
     */
    private function obtenerPorDependencia($nominas)
    {
        $resultado = [];
        
        foreach ($nominas as $nomina) {
            $nomina->load('detalles.empleado');
            
            foreach ($nomina->detalles as $detalle) {
                $dep = $detalle->empleado->dependencia ?? 'Sin Dependencia';
                
                if (!isset($resultado[$dep])) {
                    $resultado[$dep] = [
                        'nombre' => $dep,
                        'empleados' => 0,
                        'devengado' => 0,
                        'deducciones' => 0,
                        'neto' => 0,
                    ];
                }
                
                $resultado[$dep]['empleados']++;
                $resultado[$dep]['devengado'] += $detalle->total_devengado;
                $resultado[$dep]['deducciones'] += $detalle->total_deducciones;
                $resultado[$dep]['neto'] += $detalle->total_neto;
            }
        }
        
        // Calcular porcentajes
        $totalNeto = array_sum(array_column($resultado, 'neto'));
        
        foreach ($resultado as &$dep) {
            $dep['porcentaje'] = $totalNeto > 0 ? ($dep['neto'] / $totalNeto) * 100 : 0;
        }
        
        return array_values($resultado);
    }

    /**
     * Obtener composición de devengados
     */
    private function obtenerComposicionDevengados($nominas)
    {
        $total = $nominas->sum('total_devengado');
        
        return [
            [
                'nombre' => 'Salario Básico',
                'valor' => $nominas->sum(function($n) {
                    return $n->detalles->sum('salario_basico');
                }),
                'porcentaje' => $total > 0 ? ($nominas->sum(function($n) {
                    return $n->detalles->sum('salario_basico');
                }) / $total) * 100 : 0,
            ],
        ];
    }

    /**
     * Obtener composición de deducciones
     */
    private function obtenerComposicionDeducciones($nominas)
    {
        $total = $nominas->sum('total_deducciones');
        
        return [
            [
                'nombre' => 'Salud (4%)',
                'valor' => $nominas->sum(function($n) {
                    return $n->detalles->sum('aporte_salud');
                }),
                'porcentaje' => $total > 0 ? ($nominas->sum(function($n) {
                    return $n->detalles->sum('aporte_salud');
                }) / $total) * 100 : 0,
            ],
        ];
    }

    /**
     * Obtener rangos salariales
     */
    private function obtenerRangosSalariales($nominas)
    {
        return [
            [
                'descripcion' => '1 - 2 SMLV',
                'empleados' => 0,
                'porcentaje_empleados' => 0,
                'costo' => 0,
                'porcentaje_costo' => 0,
            ],
        ];
    }

    /**
     * Generar alertas
     */
    private function generarAlertas($nominas)
    {
        return [];
    }

    /**
     * Calcular totales generales
     */
    private function calcularTotalesGenerales($nominas)
    {
        return [
            'devengado' => $nominas->sum('total_devengado'),
            'deducciones' => $nominas->sum('total_deducciones'),
            'neto' => $nominas->sum('total_neto'),
            'aportes_empleador' => $this->calcularSeguridadSocialTotal($nominas) + $this->calcularParafiscalesTotal($nominas),
            'costo_total' => $this->calcularCostoTotalEmpleador($nominas),
        ];
    }
}