<?php

namespace App\Modules\Nomina\Services\Reportes;

use App\Modules\Nomina\Models\Nomina;
use Maatwebsite\Excel\Facades\Excel;

class ConsolidadosService
{
    /**
     * Generar consolidado de seguridad social
     */
    public function consolidadoSeguridadSocial(Nomina $nomina): array
    {
        $detalles = $nomina->detalles()->with('empleado')->get();
        
        // Agrupar por entidad de salud
        $porEPS = $detalles->groupBy('empleado.eps')->map(function($grupo) {
            return [
                'cantidad_empleados' => $grupo->count(),
                'aporte_empleado' => $grupo->sum('aporte_salud_empleado'),
                'aporte_empleador' => $grupo->sum('aporte_salud_empleador'),
                'total_aportes' => $grupo->sum('aporte_salud_empleado') + $grupo->sum('aporte_salud_empleador'),
            ];
        });
        
        // Agrupar por fondo de pensión
        $porPension = $detalles->groupBy('empleado.fondo_pension')->map(function($grupo) {
            return [
                'cantidad_empleados' => $grupo->count(),
                'aporte_empleado' => $grupo->sum('aporte_pension_empleado'),
                'aporte_empleador' => $grupo->sum('aporte_pension_empleador'),
                'fsp' => $grupo->sum('fondo_solidaridad_empleado'),
                'total_aportes' => $grupo->sum('aporte_pension_empleado') + 
                                  $grupo->sum('aporte_pension_empleador') +
                                  $grupo->sum('fondo_solidaridad_empleado'),
            ];
        });
        
        // Agrupar por ARL
        $porARL = $detalles->groupBy('empleado.arl')->map(function($grupo) {
            return [
                'cantidad_empleados' => $grupo->count(),
                'total_aportes' => $grupo->sum('aporte_arl_empleador'),
            ];
        });
        
        // Agrupar por caja de compensación
        $porCaja = $detalles->groupBy('empleado.caja_compensacion')->map(function($grupo) {
            return [
                'cantidad_empleados' => $grupo->count(),
                'aporte_caja' => $grupo->sum('aporte_caja'),
                'aporte_icbf' => $grupo->sum('aporte_icbf'),
                'aporte_sena' => $grupo->sum('aporte_sena'),
                'total_aportes' => $grupo->sum('aporte_caja') + 
                                  $grupo->sum('aporte_icbf') + 
                                  $grupo->sum('aporte_sena'),
            ];
        });
        
        return [
            'nomina' => [
                'numero' => $nomina->numero_nomina,
                'nombre' => $nomina->nombre,
                'periodo' => $nomina->fecha_inicio->format('Y-m'),
            ],
            'totales' => [
                'empleados' => $nomina->numero_empleados,
                'salud_empleado' => $nomina->total_salud_empleado,
                'salud_empleador' => $nomina->total_salud_empleador,
                'pension_empleado' => $nomina->total_pension_empleado,
                'pension_empleador' => $nomina->total_pension_empleador,
                'fsp' => $nomina->total_fsp_empleado,
                'arl' => $nomina->total_arl_empleador,
                'sena' => $nomina->total_sena,
                'icbf' => $nomina->total_icbf,
                'caja' => $nomina->total_caja,
            ],
            'por_eps' => $porEPS,
            'por_pension' => $porPension,
            'por_arl' => $porARL,
            'por_caja' => $porCaja,
        ];
    }
    
    /**
     * Generar archivo plano para PILA
     */
    public function generarArchivoPILA(Nomina $nomina): string
    {
        $detalles = $nomina->detalles()->with('empleado')->get();
        $lineas = [];
        
        // Encabezado del archivo
        $lineas[] = $this->generarEncabezadoPILA($nomina);
        
        // Detalle por cada empleado
        foreach ($detalles as $detalle) {
            $lineas[] = $this->generarLineaPILA($detalle);
        }
        
        return implode("\r\n", $lineas);
    }
    
    /**
     * Generar encabezado del archivo PILA
     */
    protected function generarEncabezadoPILA(Nomina $nomina): string
    {
        // Formato específico de PILA
        // Tipo de registro: 1 (encabezado)
        // ... campos según especificación PILA
        
        return sprintf(
            "1%s%s%s%s",
            str_pad(config('nomina.empresa.nit'), 16, '0', STR_PAD_LEFT),
            $nomina->fecha_inicio->format('Ym'),
            str_pad($nomina->numero_empleados, 5, '0', STR_PAD_LEFT),
            str_pad(number_format($nomina->total_devengado, 0, '', ''), 15, '0', STR_PAD_LEFT)
        );
    }
    
    /**
     * Generar línea PILA por empleado
     */
    protected function generarLineaPILA($detalle): string
    {
        $empleado = $detalle->empleado;
        
        // Tipo de registro: 2 (detalle)
        // ... campos según especificación PILA
        
        return sprintf(
            "2%s%s%s%s%s%s%s",
            str_pad($empleado->tipo_documento, 2),
            str_pad($empleado->numero_documento, 16, '0', STR_PAD_LEFT),
            str_pad(substr($empleado->primer_apellido, 0, 20), 20),
            str_pad(substr($empleado->segundo_apellido ?? '', 0, 30), 30),
            str_pad(substr($empleado->primer_nombre, 0, 20), 20),
            str_pad(number_format($detalle->base_seguridad_social, 0, '', ''), 9, '0', STR_PAD_LEFT),
            str_pad($empleado->eps_codigo ?? '', 6)
        );
    }
    
    /**
     * Generar reporte de nómina por centro de costo
     */
    public function reportePorCentroCosto(Nomina $nomina): array
    {
        $detalles = $nomina->detalles()->with(['empleado.centrosCostoActivos'])->get();
        
        $porCentro = [];
        
        foreach ($detalles as $detalle) {
            $centros = $detalle->empleado->centrosCostoActivos;
            
            foreach ($centros as $centro) {
                $porcentaje = $centro->pivot->porcentaje / 100;
                
                if (!isset($porCentro[$centro->id])) {
                    $porCentro[$centro->id] = [
                        'codigo' => $centro->codigo,
                        'nombre' => $centro->nombre,
                        'empleados' => 0,
                        'total_devengado' => 0,
                        'total_deducciones' => 0,
                        'total_neto' => 0,
                        'costo_empleador' => 0,
                    ];
                }
                
                $porCentro[$centro->id]['empleados']++;
                $porCentro[$centro->id]['total_devengado'] += $detalle->total_devengado * $porcentaje;
                $porCentro[$centro->id]['total_deducciones'] += $detalle->total_deducciones * $porcentaje;
                $porCentro[$centro->id]['total_neto'] += $detalle->total_neto * $porcentaje;
                $porCentro[$centro->id]['costo_empleador'] += $detalle->costo_total_empleador * $porcentaje;
            }
        }
        
        return [
            'nomina' => [
                'numero' => $nomina->numero_nomina,
                'nombre' => $nomina->nombre,
            ],
            'centros_costo' => array_values($porCentro),
        ];
    }
    
    /**
     * Generar reporte ejecutivo
     */
    public function reporteEjecutivo(Nomina $nomina): array
    {
        return [
            'resumen_general' => [
                'numero_empleados' => $nomina->numero_empleados,
                'total_devengado' => $nomina->total_devengado,
                'total_deducciones' => $nomina->total_deducciones,
                'total_neto' => $nomina->total_neto,
                'costo_empleador' => $nomina->costo_total_empleador,
            ],
            'seguridad_social' => [
                'empleado' => [
                    'salud' => $nomina->total_salud_empleado,
                    'pension' => $nomina->total_pension_empleado,
                    'fsp' => $nomina->total_fsp_empleado,
                    'total' => $nomina->total_salud_empleado + 
                              $nomina->total_pension_empleado + 
                              $nomina->total_fsp_empleado,
                ],
                'empleador' => [
                    'salud' => $nomina->total_salud_empleador,
                    'pension' => $nomina->total_pension_empleador,
                    'arl' => $nomina->total_arl_empleador,
                    'total' => $nomina->total_salud_empleador + 
                              $nomina->total_pension_empleador + 
                              $nomina->total_arl_empleador,
                ],
            ],
            'parafiscales' => [
                'sena' => $nomina->total_sena,
                'icbf' => $nomina->total_icbf,
                'caja' => $nomina->total_caja,
                'total' => $nomina->total_sena + $nomina->total_icbf + $nomina->total_caja,
            ],
            'provisiones' => [
                'cesantias' => $nomina->total_cesantias,
                'intereses' => $nomina->total_intereses_cesantias,
                'prima' => $nomina->total_prima,
                'vacaciones' => $nomina->total_vacaciones,
                'total' => $nomina->total_cesantias + 
                          $nomina->total_intereses_cesantias + 
                          $nomina->total_prima + 
                          $nomina->total_vacaciones,
            ],
            'distribucion' => [
                'por_centro_costo' => $this->reportePorCentroCosto($nomina)['centros_costo'],
            ],
        ];
    }
    
    /**
     * Exportar consolidado a Excel
     */
    public function exportarExcel(Nomina $nomina, string $tipo = 'completo'): string
    {
        $datos = match($tipo) {
            'seguridad_social' => $this->consolidadoSeguridadSocial($nomina),
            'centro_costo' => $this->reportePorCentroCosto($nomina),
            'ejecutivo' => $this->reporteEjecutivo($nomina),
            default => $this->reporteEjecutivo($nomina),
        };
        
        // Implementar exportación con Maatwebsite\Excel
        // return Excel::download(new NominaExport($datos), "nomina_{$nomina->numero_nomina}.xlsx");
        
        return "nomina_{$nomina->numero_nomina}.xlsx";
    }
}