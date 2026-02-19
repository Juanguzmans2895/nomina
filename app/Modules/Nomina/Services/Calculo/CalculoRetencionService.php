<?php

namespace App\Modules\Nomina\Services\Calculo;

class CalculoRetencionService
{
    /**
     * Calcular retención en la fuente sobre salarios
     * Según tabla de UVT colombiana
     */
    public function calcular(float $ingresoMensual, array $opciones = []): array
    {
        $uvt = config('nomina.uvt.valor_actual');
        
        // Deducciones permitidas
        $deducciones = $this->calcularDeducciones($ingresoMensual, $opciones);
        
        // Renta gravable
        $rentaGravable = $ingresoMensual - $deducciones['total_deducciones'];
        
        // Si la renta gravable es negativa o cero, no hay retención
        if ($rentaGravable <= 0) {
            return [
                'ingreso_mensual' => $ingresoMensual,
                'deducciones' => $deducciones,
                'renta_gravable' => 0,
                'renta_gravable_uvt' => 0,
                'retencion' => 0,
                'retencion_uvt' => 0,
                'porcentaje_efectivo' => 0,
                'exento' => true,
            ];
        }
        
        // Convertir a UVT
        $rentaGravableUVT = $rentaGravable / $uvt;
        
        // Calcular retención según tabla
        $retencionUVT = $this->calcularRetencionPorTabla($rentaGravableUVT);
        
        // Convertir a pesos
        $retencion = $retencionUVT * $uvt;
        
        // Porcentaje efectivo
        $porcentajeEfectivo = $ingresoMensual > 0 
            ? ($retencion / $ingresoMensual) * 100 
            : 0;
        
        return [
            'ingreso_mensual' => round($ingresoMensual, 2),
            'deducciones' => $deducciones,
            'renta_gravable' => round($rentaGravable, 2),
            'renta_gravable_uvt' => round($rentaGravableUVT, 2),
            'retencion' => round($retencion, 2),
            'retencion_uvt' => round($retencionUVT, 2),
            'porcentaje_efectivo' => round($porcentajeEfectivo, 2),
            'exento' => false,
        ];
    }
    
    /**
     * Calcular deducciones permitidas
     */
    protected function calcularDeducciones(float $ingresoMensual, array $opciones): array
    {
        $uvt = config('nomina.uvt.valor_actual');
        $deducciones = [];
        
        // Aportes obligatorios a salud y pensión
        if ($opciones['incluir_salud'] ?? true) {
            $salud = $ingresoMensual * 0.04; // 4%
            $deducciones['salud'] = $salud;
        }
        
        if ($opciones['incluir_pension'] ?? true) {
            $pension = $ingresoMensual * 0.04; // 4%
            $deducciones['pension'] = $pension;
        }
        
        // Dependientes (32 UVT por dependiente, máximo según ley)
        $numeroDependientes = $opciones['numero_dependientes'] ?? 0;
        if ($numeroDependientes > 0) {
            $valorDependiente = 32 * $uvt;
            $deducciones['dependientes'] = $valorDependiente * $numeroDependientes;
        }
        
        // Intereses de vivienda (máximo 1200 UVT anuales = 100 UVT mensuales)
        $interesesVivienda = $opciones['intereses_vivienda'] ?? 0;
        if ($interesesVivienda > 0) {
            $limiteVivienda = 100 * $uvt;
            $deducciones['intereses_vivienda'] = min($interesesVivienda, $limiteVivienda);
        }
        
        // Medicina prepagada (máximo 192 UVT anuales = 16 UVT mensuales)
        $medicinaPrep = $opciones['medicina_prepagada'] ?? 0;
        if ($medicinaPrep > 0) {
            $limiteMedicina = 16 * $uvt;
            $deducciones['medicina_prepagada'] = min($medicinaPrep, $limiteMedicina);
        }
        
        // Aportes voluntarios a pensión
        $aportesVoluntarios = $opciones['aportes_voluntarios_pension'] ?? 0;
        if ($aportesVoluntarios > 0) {
            // Máximo 25% del ingreso laboral
            $limiteVoluntario = $ingresoMensual * 0.25;
            $deducciones['aportes_voluntarios'] = min($aportesVoluntarios, $limiteVoluntario);
        }
        
        $totalDeducciones = array_sum($deducciones);
        
        return [
            'detalle' => $deducciones,
            'total_deducciones' => round($totalDeducciones, 2),
        ];
    }
    
    /**
     * Calcular retención según tabla de UVT
     */
    protected function calcularRetencionPorTabla(float $rentaUVT): float
    {
        $tabla = config('nomina.retencion.tabla_uvt');
        
        // Encontrar el rango aplicable
        foreach ($tabla as $rango) {
            $limiteInferior = $rango[0];
            $limiteSuperior = $rango[1];
            $tarifaMarginal = $rango[2];
            $retencionDesde = $rango[3];
            
            // Verificar si está en este rango
            if ($limiteSuperior === 'INF') {
                // Último rango
                if ($rentaUVT > $limiteInferior) {
                    $exceso = $rentaUVT - $limiteInferior;
                    return $retencionDesde + ($exceso * ($tarifaMarginal / 100));
                }
            } else {
                if ($rentaUVT > $limiteInferior && $rentaUVT <= $limiteSuperior) {
                    $exceso = $rentaUVT - $limiteInferior;
                    return $retencionDesde + ($exceso * ($tarifaMarginal / 100));
                }
            }
        }
        
        return 0; // No aplica retención
    }
    
    /**
     * Calcular retención anual proyectada
     */
    public function calcularRetencionAnual(float $ingresoMensual, array $opciones = []): array
    {
        $retencionMensual = $this->calcular($ingresoMensual, $opciones);
        
        return [
            'ingreso_anual' => $ingresoMensual * 12,
            'retencion_anual' => $retencionMensual['retencion'] * 12,
            'retencion_mensual' => $retencionMensual['retencion'],
            'porcentaje_efectivo' => $retencionMensual['porcentaje_efectivo'],
        ];
    }
    
    /**
     * Verificar si un ingreso está exento de retención
     */
    public function estaExento(float $ingresoMensual): bool
    {
        $uvt = config('nomina.uvt.valor_actual');
        $limiteExencion = 95 * $uvt; // Según tabla, menos de 95 UVT no paga
        
        return $ingresoMensual < $limiteExencion;
    }
}