<?php

namespace App\Modules\Nomina\Services\Reportes;

use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Models\NominaDetalle;
use App\Modules\Nomina\Models\Empleado;
use Barryvdh\DomPDF\Facade\Pdf;

class DesprendibleService
{
    /**
     * Generar desprendible individual
     */
    public function generarDesprendible(NominaDetalle $detalle): array
    {
        $nomina = $detalle->nomina;
        $empleado = $detalle->empleado;
        
        $datos = [
            // Información de la Empresa
            'empresa' => [
                'nombre' => config('app.name'),
                'nit' => config('nomina.empresa.nit'),
                'direccion' => config('nomina.empresa.direccion'),
                'ciudad' => config('nomina.empresa.ciudad'),
            ],
            
            // Información del Empleado
            'empleado' => [
                'nombre' => $empleado->nombre_completo,
                'documento' => $empleado->numero_documento,
                'cargo' => $empleado->cargo,
                'codigo' => $empleado->codigo_empleado,
                'dependencia' => $empleado->dependencia,
                'fecha_ingreso' => $empleado->fecha_ingreso->format('d/m/Y'),
            ],
            
            // Información de la Nómina
            'nomina' => [
                'numero' => $nomina->numero_nomina,
                'nombre' => $nomina->nombre,
                'periodo' => $nomina->fecha_inicio->format('d/m/Y') . ' - ' . $nomina->fecha_fin->format('d/m/Y'),
                'fecha_pago' => $nomina->fecha_pago?->format('d/m/Y'),
            ],
            
            // Devengados
            'devengados' => [
                ['concepto' => 'Salario Básico', 'valor' => $detalle->salario_basico],
                ['concepto' => 'Auxilio de Transporte', 'valor' => $detalle->auxilio_transporte],
                ['concepto' => 'Horas Extras', 'valor' => $detalle->horas_extras],
                ['concepto' => 'Recargos', 'valor' => $detalle->recargos],
                ['concepto' => 'Comisiones', 'valor' => $detalle->comisiones],
                ['concepto' => 'Bonificaciones', 'valor' => $detalle->bonificaciones],
                ['concepto' => 'Otros Ingresos', 'valor' => $detalle->otros_ingresos],
            ],
            'total_devengado' => $detalle->total_devengado,
            
            // Deducciones
            'deducciones' => [
                ['concepto' => 'Aporte Salud (4%)', 'valor' => $detalle->aporte_salud_empleado],
                ['concepto' => 'Aporte Pensión (4%)', 'valor' => $detalle->aporte_pension_empleado],
                ['concepto' => 'Fondo Solidaridad Pensional', 'valor' => $detalle->fondo_solidaridad_empleado],
                ['concepto' => 'Retención en la Fuente', 'valor' => $detalle->retencion_fuente],
                ['concepto' => 'Préstamos', 'valor' => $detalle->prestamos],
                ['concepto' => 'Embargos', 'valor' => $detalle->embargos],
                ['concepto' => 'Otros Descuentos', 'valor' => $detalle->otros_descuentos],
            ],
            'total_deducciones' => $detalle->total_deducciones,
            
            // Total Neto
            'neto_pagar' => $detalle->total_neto,
            
            // Seguridad Social (Empleador)
            'seguridad_social_empleador' => [
                ['concepto' => 'Aporte Salud (8.5%)', 'valor' => $detalle->aporte_salud_empleador],
                ['concepto' => 'Aporte Pensión (12%)', 'valor' => $detalle->aporte_pension_empleador],
                ['concepto' => 'ARL', 'valor' => $detalle->aporte_arl_empleador],
            ],
            
            // Parafiscales (Empleador)
            'parafiscales' => [
                ['concepto' => 'SENA (2%)', 'valor' => $detalle->aporte_sena],
                ['concepto' => 'ICBF (3%)', 'valor' => $detalle->aporte_icbf],
                ['concepto' => 'Caja Compensación (4%)', 'valor' => $detalle->aporte_caja],
            ],
            
            // Provisiones (Empleador)
            'provisiones' => [
                ['concepto' => 'Cesantías', 'valor' => $detalle->provision_cesantias],
                ['concepto' => 'Intereses Cesantías', 'valor' => $detalle->provision_intereses_cesantias],
                ['concepto' => 'Prima de Servicios', 'valor' => $detalle->provision_prima],
                ['concepto' => 'Vacaciones', 'valor' => $detalle->provision_vacaciones],
            ],
            
            // Costo Total Empleador
            'costo_empleador' => $detalle->costo_total_empleador,
            
            // Información Bancaria
            'banco' => [
                'entidad' => $empleado->banco,
                'tipo_cuenta' => $empleado->tipo_cuenta,
                'numero_cuenta' => $empleado->numero_cuenta,
            ],
            
            // Información Adicional
            'dias_trabajados' => $detalle->dias_trabajados,
            'base_seguridad_social' => $detalle->base_seguridad_social,
        ];
        
        return $datos;
    }
    
    /**
     * Generar PDF del desprendible
     */
    public function generarPDF(NominaDetalle $detalle): \Barryvdh\DomPDF\PDF
    {
        $datos = $this->generarDesprendible($detalle);
        
        return Pdf::loadView('nomina.reportes.desprendible', $datos)
            ->setPaper('letter', 'portrait');
    }
    
    /**
     * Generar desprendibles para toda la nómina
     */
    public function generarDesprendiblesMasivos(Nomina $nomina): array
    {
        $detalles = $nomina->detalles()->with('empleado')->get();
        $archivos = [];
        
        foreach ($detalles as $detalle) {
            $pdf = $this->generarPDF($detalle);
            $nombreArchivo = "desprendible_{$detalle->empleado->numero_documento}_{$nomina->numero_nomina}.pdf";
            
            $archivos[] = [
                'empleado' => $detalle->empleado->nombre_completo,
                'documento' => $detalle->empleado->numero_documento,
                'archivo' => $nombreArchivo,
                'pdf' => $pdf,
            ];
        }
        
        return $archivos;
    }
    
    /**
     * Generar desprendible en formato HTML
     */
    public function generarHTML(NominaDetalle $detalle): string
    {
        $datos = $this->generarDesprendible($detalle);
        return view('nomina.reportes.desprendible', $datos)->render();
    }
    
    /**
     * Enviar desprendible por email
     */
    public function enviarPorEmail(NominaDetalle $detalle): bool
    {
        $empleado = $detalle->empleado;
        $pdf = $this->generarPDF($detalle);
        
        // Implementar envío de email
        // Mail::to($empleado->email)->send(new DesprendibleEmail($pdf));
        
        return true;
    }
}