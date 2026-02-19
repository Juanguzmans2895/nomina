<?php

namespace App\Modules\Nomina\Services\Calculo;

class CalculoParafiscalesService
{
    /**
     * Calcular aportes parafiscales
     * 
     * Los parafiscales aplican solo para salarios <= 10 SMLV
     * y se calculan sobre la base de cotización
     */
    public function calcular(float $salarioBase, array $opciones = []): array
    {
        $smlv = config('nomina.smlv.valor_actual');
        $exencionSalarios = config('nomina.parafiscales.exencion_salarios', 10);
        
        // Verificar si está exento (salarios > 10 SMLV)
        $estaExento = $salarioBase > ($smlv * $exencionSalarios);
        
        if ($estaExento) {
            return [
                'exento' => true,
                'aporte_sena' => 0,
                'aporte_icbf' => 0,
                'aporte_caja' => 0,
                'total_parafiscales' => 0,
                'base_calculo' => $salarioBase,
            ];
        }
        
        // Base de cálculo para parafiscales
        $baseCalculo = $salarioBase;
        
        // Calcular cada aporte
        $sena = $baseCalculo * (config('nomina.parafiscales.sena') / 100);
        $icbf = $baseCalculo * (config('nomina.parafiscales.icbf') / 100);
        $caja = $baseCalculo * (config('nomina.parafiscales.caja_compensacion') / 100);
        
        return [
            'exento' => false,
            'aporte_sena' => round($sena, 2),
            'aporte_icbf' => round($icbf, 2),
            'aporte_caja' => round($caja, 2),
            'total_parafiscales' => round($sena + $icbf + $caja, 2),
            'base_calculo' => $baseCalculo,
        ];
    }
    
    /**
     * Verificar si un salario está exento de parafiscales
     */
    public function estaExento(float $salarioBase): bool
    {
        $smlv = config('nomina.smlv.valor_actual');
        $exencionSalarios = config('nomina.parafiscales.exencion_salarios', 10);
        
        return $salarioBase > ($smlv * $exencionSalarios);
    }
    
    /**
     * Obtener el límite de exención
     */
    public function getLimiteExencion(): float
    {
        $smlv = config('nomina.smlv.valor_actual');
        $exencionSalarios = config('nomina.parafiscales.exencion_salarios', 10);
        
        return $smlv * $exencionSalarios;
    }
}