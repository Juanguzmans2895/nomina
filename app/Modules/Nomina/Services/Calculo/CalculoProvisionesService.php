<?php

namespace App\Modules\Nomina\Services\Calculo;

class CalculoProvisionesService
{
    /**
     * Calcular todas las provisiones laborales
     */
    public function calcularTodas(float $salarioBase, array $opciones = []): array
    {
        return [
            'cesantias' => $this->calcularCesantias($salarioBase),
            'intereses_cesantias' => $this->calcularInteresesCesantias($salarioBase),
            'prima' => $this->calcularPrima($salarioBase),
            'vacaciones' => $this->calcularVacaciones($salarioBase),
            'total_provisiones' => $this->calcularTotal($salarioBase),
        ];
    }
    
    /**
     * Calcular cesantías
     * Base: Salario mensual * 8.33%
     */
    public function calcularCesantias(float $salarioBase): float
    {
        $porcentaje = config('nomina.provisiones.cesantias.porcentaje', 8.33);
        return round($salarioBase * ($porcentaje / 100), 2);
    }
    
    /**
     * Calcular intereses sobre cesantías
     * Base: 12% anual sobre el saldo de cesantías
     * Mensual: 1% sobre el salario base
     */
    public function calcularInteresesCesantias(float $salarioBase): float
    {
        $tasaAnual = config('nomina.provisiones.cesantias.intereses', 12);
        $tasaMensual = $tasaAnual / 12;
        return round($salarioBase * ($tasaMensual / 100), 2);
    }
    
    /**
     * Calcular prima de servicios
     * Base: Salario mensual * 8.33%
     * Se paga en junio y diciembre
     */
    public function calcularPrima(float $salarioBase): float
    {
        $porcentaje = config('nomina.provisiones.prima.porcentaje', 8.33);
        return round($salarioBase * ($porcentaje / 100), 2);
    }
    
    /**
     * Calcular vacaciones
     * Base: 15 días por año = 4.17% mensual
     */
    public function calcularVacaciones(float $salarioBase): float
    {
        $porcentaje = config('nomina.provisiones.vacaciones.porcentaje', 4.17);
        return round($salarioBase * ($porcentaje / 100), 2);
    }
    
    /**
     * Calcular total de provisiones mensuales
     */
    public function calcularTotal(float $salarioBase): float
    {
        $cesantias = $this->calcularCesantias($salarioBase);
        $intereses = $this->calcularInteresesCesantias($salarioBase);
        $prima = $this->calcularPrima($salarioBase);
        $vacaciones = $this->calcularVacaciones($salarioBase);
        
        return round($cesantias + $intereses + $prima + $vacaciones, 2);
    }
    
    /**
     * Calcular cesantías acumuladas por período
     * 
     * @param float $salarioBase
     * @param int $diasTrabajados Días trabajados en el año
     * @return float
     */
    public function calcularCesantiasAcumuladas(float $salarioBase, int $diasTrabajados): float
    {
        // Cesantías = (Salario * Días trabajados) / 360
        return round(($salarioBase * $diasTrabajados) / 360, 2);
    }
    
    /**
     * Calcular intereses sobre cesantías acumuladas
     * 
     * @param float $saldoCesantias Saldo acumulado de cesantías
     * @param int $diasAcumulados Días desde enero 1 o último pago
     * @return float
     */
    public function calcularInteresesSobreSaldo(float $saldoCesantias, int $diasAcumulados): float
    {
        // Intereses = (Saldo cesantías * Días * 12%) / 360
        return round(($saldoCesantias * $diasAcumulados * 0.12) / 360, 2);
    }
    
    /**
     * Calcular prima de servicios por período
     * 
     * @param float $salarioBase
     * @param int $diasTrabajados Días trabajados en el semestre
     * @return float
     */
    public function calcularPrimaPeriodo(float $salarioBase, int $diasTrabajados): float
    {
        // Prima = (Salario * Días trabajados) / 360
        return round(($salarioBase * $diasTrabajados) / 360, 2);
    }
    
    /**
     * Calcular vacaciones por período
     * 
     * @param float $salarioBase
     * @param int $diasTrabajados Días trabajados en el año
     * @return array
     */
    public function calcularVacacionesPeriodo(float $salarioBase, int $diasTrabajados): array
    {
        // 15 días hábiles por año = 1.25 días por mes
        $diasVacaciones = ($diasTrabajados / 360) * 15;
        
        // Salario diario
        $salarioDiario = $salarioBase / 30;
        
        // Valor de las vacaciones
        $valorVacaciones = $salarioDiario * $diasVacaciones;
        
        return [
            'dias_causados' => round($diasVacaciones, 2),
            'salario_diario' => round($salarioDiario, 2),
            'valor_vacaciones' => round($valorVacaciones, 2),
        ];
    }
    
    /**
     * Calcular liquidación de prestaciones sociales
     * Se usa cuando termina el contrato
     */
    public function calcularLiquidacion(
        float $salarioBase, 
        int $diasTrabajados,
        float $saldoCesantiasAnterior = 0
    ): array {
        // Cesantías del período
        $cesantias = $this->calcularCesantiasAcumuladas($salarioBase, $diasTrabajados);
        $totalCesantias = $cesantias + $saldoCesantiasAnterior;
        
        // Intereses sobre el total de cesantías
        $intereses = $this->calcularInteresesSobreSaldo($totalCesantias, $diasTrabajados);
        
        // Prima proporcional
        $prima = $this->calcularPrimaPeriodo($salarioBase, $diasTrabajados);
        
        // Vacaciones proporcionales
        $vacaciones = $this->calcularVacacionesPeriodo($salarioBase, $diasTrabajados);
        
        return [
            'cesantias' => round($totalCesantias, 2),
            'intereses_cesantias' => round($intereses, 2),
            'prima' => round($prima, 2),
            'vacaciones' => $vacaciones['valor_vacaciones'],
            'dias_vacaciones' => $vacaciones['dias_causados'],
            'total_liquidacion' => round(
                $totalCesantias + $intereses + $prima + $vacaciones['valor_vacaciones'], 
                2
            ),
        ];
    }
    
    /**
     * Calcular porcentaje total de provisiones sobre el salario
     */
    public function getPorcentajeTotal(): float
    {
        $cesantias = config('nomina.provisiones.cesantias.porcentaje', 8.33);
        $intereses = 1.0; // 12% anual = 1% mensual
        $prima = config('nomina.provisiones.prima.porcentaje', 8.33);
        $vacaciones = config('nomina.provisiones.vacaciones.porcentaje', 4.17);
        
        return round($cesantias + $intereses + $prima + $vacaciones, 2);
    }
}