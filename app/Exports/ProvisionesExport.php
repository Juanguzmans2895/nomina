<?php

namespace App\Exports;

use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\Provision;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class ProvisionesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Obtener todos los empleados activos con su última provisión mensual
        $empleados = Empleado::where('estado', 'activo')
            ->orderBy('primer_apellido')
            ->orderBy('primer_nombre')
            ->get();
        
        $data = collect();
        
        foreach ($empleados as $empleado) {
            // Obtener la última provisión mensual del empleado
            $provision = Provision::where('empleado_id', $empleado->id)
                ->where('tipo_provision', 'mensual')
                ->orderBy('fecha_causacion', 'desc')
                ->first();
            
            // Consolidar datos
            $data->push((object)[
                'empleado' => $empleado,
                'provision' => $provision,
            ]);
        }
        
        return $data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Documento',
            'Nombre Completo',
            'Cargo',
            'Fecha Ingreso',
            'Antigüedad (Meses)',
            'Salario Básico',
            'Cesantías Saldo',
            'Cesantías Causado Mes',
            'Cesantías Pagado',
            'Intereses Saldo',
            'Intereses Causado Mes',
            'Intereses Pagado',
            'Prima Saldo',
            'Prima Causado Mes',
            'Prima Pagado',
            'Vacaciones Saldo',
            'Vacaciones Causado Mes',
            'Vacaciones Pagado',
            'Total Provisiones',
            'Causación Mensual Total',
            'Última Actualización',
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        $empleado = $row->empleado;
        $provision = $row->provision;
        $antiguedadMeses = $empleado->fecha_ingreso->diffInMonths(now());
        
        if (!$provision) {
            // Si no tiene provisión, calcular valores estimados
            $salarioBasico = $empleado->salario_basico;
            $cesantiasMensual = $salarioBasico * 0.0833;
            $saldoCesantias = $cesantiasMensual * $antiguedadMeses;
            $interesesMensual = ($saldoCesantias * 0.12) / 12;
            $saldoIntereses = $interesesMensual * $antiguedadMeses;
            $primaMensual = $salarioBasico * 0.0833;
            $saldoPrima = $primaMensual * $antiguedadMeses;
            $vacacionesMensual = $salarioBasico * 0.0417;
            $saldoVacaciones = $vacacionesMensual * $antiguedadMeses;
            
            return [
                $empleado->numero_documento,
                $empleado->nombre_completo,
                $empleado->cargo ?? 'N/A',
                $empleado->fecha_ingreso->format('d/m/Y'),
                $antiguedadMeses,
                $salarioBasico,
                $saldoCesantias,
                $cesantiasMensual,
                0,
                $saldoIntereses,
                $interesesMensual,
                0,
                $saldoPrima,
                $primaMensual,
                0,
                $saldoVacaciones,
                $vacacionesMensual,
                0,
                $saldoCesantias + $saldoIntereses + $saldoPrima + $saldoVacaciones,
                $cesantiasMensual + $interesesMensual + $primaMensual + $vacacionesMensual,
                'Sin provisión registrada',
            ];
        }
        
        // Saldos
        $saldoCesantias = $provision->saldo_cesantias ?? 0;
        $saldoIntereses = $provision->saldo_intereses ?? 0;
        $saldoPrima = $provision->saldo_prima ?? 0;
        $saldoVacaciones = $provision->saldo_vacaciones ?? 0;
        
        // Causaciones mensuales
        $causacionCesantias = $provision->valor_causado_cesantias ?? 0;
        $causacionIntereses = $provision->valor_causado_intereses ?? 0;
        $causacionPrima = $provision->valor_causado_prima ?? 0;
        $causacionVacaciones = $provision->valor_causado_vacaciones ?? 0;
        
        // Valores pagados
        $pagadoCesantias = $provision->valor_pagado_cesantias ?? 0;
        $pagadoIntereses = $provision->valor_pagado_intereses ?? 0;
        $pagadoPrima = $provision->valor_pagado_prima ?? 0;
        $pagadoVacaciones = $provision->valor_pagado_vacaciones ?? 0;
        
        // Totales
        $totalProvisiones = $saldoCesantias + $saldoIntereses + $saldoPrima + $saldoVacaciones;
        $causacionMensualTotal = $causacionCesantias + $causacionIntereses + $causacionPrima + $causacionVacaciones;

        return [
            $empleado->numero_documento,
            $empleado->nombre_completo,
            $empleado->cargo ?? 'N/A',
            $empleado->fecha_ingreso->format('d/m/Y'),
            $antiguedadMeses,
            $empleado->salario_basico,
            $saldoCesantias,
            $causacionCesantias,
            $pagadoCesantias,
            $saldoIntereses,
            $causacionIntereses,
            $pagadoIntereses,
            $saldoPrima,
            $causacionPrima,
            $pagadoPrima,
            $saldoVacaciones,
            $causacionVacaciones,
            $pagadoVacaciones,
            $totalProvisiones,
            $causacionMensualTotal,
            $provision->updated_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para el encabezado
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669']
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Provisiones ' . now()->format('Y-m');
    }
}