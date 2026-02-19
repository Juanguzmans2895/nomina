<?php

namespace App\Exports;

use App\Modules\Nomina\Models\AsientoContableNomina;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AsientosContablesExport implements WithMultipleSheets
{
    protected $asientos;
    
    public function __construct($asientos = null)
    {
        $this->asientos = $asientos ?? AsientoContableNomina::with('detalles')
            ->orderBy('fecha_asiento', 'desc')
            ->get();
    }
    
    public function sheets(): array
    {
        return [
            new AsientosResumenSheet($this->asientos),
            new AsientosDetalleSheet($this->asientos),
        ];
    }
}

class AsientosResumenSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $asientos;
    
    public function __construct($asientos)
    {
        $this->asientos = $asientos;
    }
    
    public function collection()
    {
        return $this->asientos;
    }
    
    public function headings(): array
    {
        return [
            'Número Asiento',
            'Fecha',
            'Tipo',
            'Descripción',
            'Total Débitos',
            'Total Créditos',
            'Diferencia',
            'Estado',
            'Cuadrado',
        ];
    }
    
    public function map($asiento): array
    {
        $diferencia = $asiento->total_debitos - $asiento->total_creditos;
        $cuadrado = abs($diferencia) < 0.01 ? 'SÍ' : 'NO';
        
        return [
            $asiento->numero_asiento,
            $asiento->fecha_asiento->format('d/m/Y'),
            ucfirst(str_replace('_', ' ', $asiento->tipo_asiento)),
            $asiento->descripcion,
            $asiento->total_debitos,
            $asiento->total_creditos,
            $diferencia,
            ucfirst($asiento->estado),
            $cuadrado,
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1e40af']
                ],
            ],
        ];
    }
    
    public function title(): string
    {
        return 'Resumen Asientos';
    }
}

class AsientosDetalleSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $asientos;
    
    public function __construct($asientos)
    {
        $this->asientos = $asientos;
    }
    
    public function collection()
    {
        $detalles = collect();
        
        foreach ($this->asientos as $asiento) {
            foreach ($asiento->detalles as $detalle) {
                $detalles->push((object)[
                    'asiento' => $asiento,
                    'detalle' => $detalle,
                ]);
            }
        }
        
        return $detalles;
    }
    
    public function headings(): array
    {
        return [
            'Número Asiento',
            'Fecha',
            'Cuenta',
            'Nombre Cuenta',
            'Tercero',
            'Débito',
            'Crédito',
            'Centro Costo',
        ];
    }
    
    public function map($row): array
    {
        return [
            $row->asiento->numero_asiento,
            $row->asiento->fecha_asiento->format('d/m/Y'),
            $row->detalle->cuenta_contable,
            $row->detalle->nombre_cuenta,
            $row->detalle->tercero ?? '',
            $row->detalle->debito,
            $row->detalle->credito,
            $row->detalle->centro_costo ?? '',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669']
                ],
            ],
        ];
    }
    
    public function title(): string
    {
        return 'Detalle Movimientos';
    }
}