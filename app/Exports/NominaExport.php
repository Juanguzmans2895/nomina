<?php

namespace App\Exports;

use App\Modules\Nomina\Models\Nomina;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NominaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $nomina;

    public function __construct(Nomina $nomina)
    {
        $this->nomina = $nomina;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->nomina->detallesNomina()->with('empleado')->get();
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
            'Salario Básico',
            'Total Devengado',
            'Salud Empleado',
            'Pensión Empleado',
            'Total Deducciones',
            'Retención Fuente',
            'Total Neto',
            'Salud Empleador',
            'Pensión Empleador',
            'ARL Empleador',
            'Parafiscales',
            'Provisiones',
            'Costo Total Empleador',
        ];
    }

    /**
     * @param mixed $detalle
     * @return array
     */
    public function map($detalle): array
    {
        $salarioBasico = $detalle->salario_basico;
        $saludEmpleador = $detalle->salud_empleador ?? ($salarioBasico * 0.085);
        $pensionEmpleador = $detalle->pension_empleador ?? ($salarioBasico * 0.12);
        $arlEmpleador = $detalle->arl_empleador ?? ($salarioBasico * 0.00522);
        $parafiscales = $salarioBasico * 0.09; // 9%
        $provisiones = $salarioBasico * 0.2167; // 21.67%
        
        $costoTotal = $salarioBasico + $saludEmpleador + $pensionEmpleador + $arlEmpleador + $parafiscales + $provisiones;

        return [
            $detalle->empleado->numero_documento,
            $detalle->empleado->nombre_completo,
            $detalle->empleado->cargo ?? 'N/A',
            $salarioBasico,
            $detalle->total_devengado,
            $detalle->salud_empleado ?? ($salarioBasico * 0.04),
            $detalle->pension_empleado ?? ($salarioBasico * 0.04),
            $detalle->total_deducciones,
            $detalle->retencion_fuente ?? 0,
            $detalle->total_neto,
            $saludEmpleador,
            $pensionEmpleador,
            $arlEmpleador,
            $parafiscales,
            $provisiones,
            $costoTotal,
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
                    'startColor' => ['rgb' => '1e40af']
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Nómina ' . $this->nomina->numero_nomina;
    }
}