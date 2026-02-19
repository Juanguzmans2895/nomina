<?php

namespace App\Modules\Nomina\Services\Calculo;

class CalculoSeguridadSocialService
{
    /**
     * Calcular aportes de seguridad social
     */
    public function calcular(float $salarioBase, array $opciones = []): array
    {
        $smlv = config('nomina.smlv.valor_actual');
        $topeSalud = config('nomina.topes.salud') * $smlv;
        $topePension = config('nomina.topes.pension') * $smlv;
        
        // Base de cotización (limitar a topes)
        $baseSalud = min($salarioBase, $topeSalud);
        $basePension = min($salarioBase, $topePension);
        
        // Aportes empleado
        $saludEmpleado = $baseSalud * (config('nomina.seguridad_social.salud.empleado') / 100);
        $pensionEmpleado = $basePension * (config('nomina.seguridad_social.pension.empleado') / 100);
        
        // Fondo de Solidaridad Pensional
        $fsp = 0;
        if ($salarioBase >= 4 * $smlv) {
            if ($salarioBase < 16 * $smlv) {
                $fsp = $basePension * 0.01; // 1%
            } elseif ($salarioBase >= 16 * $smlv && $salarioBase < 17 * $smlv) {
                $fsp = $basePension * 0.012; // 1.2%
            } elseif ($salarioBase >= 17 * $smlv && $salarioBase < 18 * $smlv) {
                $fsp = $basePension * 0.014; // 1.4%
            } elseif ($salarioBase >= 18 * $smlv && $salarioBase < 19 * $smlv) {
                $fsp = $basePension * 0.016; // 1.6%
            } elseif ($salarioBase >= 19 * $smlv && $salarioBase < 20 * $smlv) {
                $fsp = $basePension * 0.018; // 1.8%
            } else {
                $fsp = $basePension * 0.02; // 2%
            }
        }
        
        // Aportes empleador
        $saludEmpleador = $baseSalud * (config('nomina.seguridad_social.salud.empleador') / 100);
        $pensionEmpleador = $basePension * (config('nomina.seguridad_social.pension.empleador') / 100);
        
        // ARL
        $claseRiesgo = $opciones['clase_riesgo'] ?? config('nomina.seguridad_social.arl.empleador');
        $arlEmpleador = $salarioBase * ($claseRiesgo / 100);
        
        return [
            'base_salud' => $baseSalud,
            'base_pension' => $basePension,
            'aporte_salud_empleado' => round($saludEmpleado, 2),
            'aporte_pension_empleado' => round($pensionEmpleado, 2),
            'fondo_solidaridad_empleado' => round($fsp, 2),
            'aporte_salud_empleador' => round($saludEmpleador, 2),
            'aporte_pension_empleador' => round($pensionEmpleador, 2),
            'aporte_arl_empleador' => round($arlEmpleador, 2),
            'total_empleado' => round($saludEmpleado + $pensionEmpleado + $fsp, 2),
            'total_empleador' => round($saludEmpleador + $pensionEmpleador + $arlEmpleador, 2),
        ];
    }
}