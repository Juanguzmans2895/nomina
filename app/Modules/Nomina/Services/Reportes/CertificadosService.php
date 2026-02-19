<?php

namespace App\Modules\Nomina\Services\Reportes;

use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\ProvisionEmpleado;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CertificadosService
{
    /**
     * Generar certificado laboral básico
     */
    public function certificadoLaboral(Empleado $empleado, array $opciones = []): array
    {
        $fechaActual = now();
        
        $datos = [
            // Información de la Empresa
            'empresa' => [
                'nombre' => config('app.name'),
                'nit' => config('nomina.empresa.nit'),
                'direccion' => config('nomina.empresa.direccion'),
                'ciudad' => config('nomina.empresa.ciudad'),
                'telefono' => config('nomina.empresa.telefono'),
                'representante' => config('nomina.empresa.representante'),
                'cargo_representante' => config('nomina.empresa.cargo_representante'),
            ],
            
            // Información del Empleado
            'empleado' => [
                'nombre' => $empleado->nombre_completo,
                'documento' => $empleado->tipo_documento . ' ' . $empleado->numero_documento,
                'fecha_ingreso' => $empleado->fecha_ingreso->format('d/m/Y'),
                'fecha_ingreso_texto' => $this->numeroATexto($empleado->fecha_ingreso->day) . 
                    ' (' . $empleado->fecha_ingreso->day . ') días del mes de ' . 
                    $this->nombreMes($empleado->fecha_ingreso->month) . ' de ' . 
                    $this->numeroATexto($empleado->fecha_ingreso->year),
                'cargo' => $empleado->cargo,
                'dependencia' => $empleado->dependencia,
                'tipo_contrato' => $this->tipoContratoTexto($empleado->tipo_contrato),
                'salario_basico' => $empleado->salario_basico,
                'salario_texto' => $this->numeroATexto($empleado->salario_basico),
                'estado' => $empleado->estado,
            ],
            
            // Tiempo de Servicio
            'tiempo_servicio' => [
                'anios' => $empleado->anios_servicio,
                'texto' => $this->tiempoServicioTexto($empleado->fecha_ingreso),
            ],
            
            // Fecha de Expedición
            'fecha_expedicion' => $fechaActual->format('d/m/Y'),
            'fecha_expedicion_texto' => $this->numeroATexto($fechaActual->day) . 
                ' (' . $fechaActual->day . ') días del mes de ' . 
                $this->nombreMes($fechaActual->month) . ' de ' . 
                $this->numeroATexto($fechaActual->year),
            
            // Motivo del Certificado
            'motivo' => $opciones['motivo'] ?? 'A QUIEN PUEDA INTERESAR',
            
            // Información Adicional
            'incluir_salario' => $opciones['incluir_salario'] ?? true,
            'incluir_funciones' => $opciones['incluir_funciones'] ?? false,
            'funciones' => $opciones['funciones'] ?? null,
            
            // Tipo de Certificado
            'tipo' => $opciones['tipo'] ?? 'vigencia', // vigencia, retiro, ingresos
        ];
        
        return $datos;
    }
    
    /**
     * Generar certificado de ingresos y retenciones
     */
    public function certificadoIngresos(Empleado $empleado, int $anio): array
    {
        // Obtener nóminas del año
        $nominasAnio = $empleado->nominaDetalles()
            ->whereHas('nomina', function($q) use ($anio) {
                $q->whereYear('fecha_inicio', $anio);
            })
            ->with('nomina')
            ->get();
        
        $totalDevengado = $nominasAnio->sum('total_devengado');
        $totalDeducciones = $nominasAnio->sum('total_deducciones');
        $totalRetencion = $nominasAnio->sum('retencion_fuente');
        $totalSalud = $nominasAnio->sum('aporte_salud_empleado');
        $totalPension = $nominasAnio->sum('aporte_pension_empleado');
        
        $datos = [
            'empresa' => [
                'nombre' => config('app.name'),
                'nit' => config('nomina.empresa.nit'),
            ],
            'empleado' => [
                'nombre' => $empleado->nombre_completo,
                'documento' => $empleado->numero_documento,
                'direccion' => $empleado->direccion,
                'cargo' => $empleado->cargo,
            ],
            'periodo' => [
                'anio' => $anio,
                'texto' => "Enero 01 a Diciembre 31 de {$anio}",
            ],
            'ingresos' => [
                'total_devengado' => $totalDevengado,
                'total_devengado_texto' => $this->numeroATexto($totalDevengado),
            ],
            'deducciones' => [
                'salud' => $totalSalud,
                'pension' => $totalPension,
                'retencion' => $totalRetencion,
                'total' => $totalDeducciones,
            ],
            'neto' => $totalDevengado - $totalDeducciones,
            'fecha_expedicion' => now()->format('d/m/Y'),
        ];
        
        return $datos;
    }
    
    /**
     * Generar certificado de cesantías
     */
    public function certificadoCesantias(Empleado $empleado, int $anio = null): array
    {
        $anio = $anio ?? now()->year;
        
        $provision = ProvisionEmpleado::where('empleado_id', $empleado->id)
            ->where('anio', $anio)
            ->first();
        
        $datos = [
            'empresa' => [
                'nombre' => config('app.name'),
                'nit' => config('nomina.empresa.nit'),
            ],
            'empleado' => [
                'nombre' => $empleado->nombre_completo,
                'documento' => $empleado->numero_documento,
                'cargo' => $empleado->cargo,
                'fecha_ingreso' => $empleado->fecha_ingreso->format('d/m/Y'),
            ],
            'cesantias' => [
                'saldo' => $provision?->saldo_cesantias ?? 0,
                'saldo_texto' => $this->numeroATexto($provision?->saldo_cesantias ?? 0),
                'intereses' => $provision?->saldo_intereses ?? 0,
                'total' => ($provision?->saldo_cesantias ?? 0) + ($provision?->saldo_intereses ?? 0),
            ],
            'periodo' => $anio,
            'fecha_corte' => now()->format('d/m/Y'),
        ];
        
        return $datos;
    }
    
    /**
     * Generar PDF del certificado laboral
     */
    public function generarPDFLaboral(Empleado $empleado, array $opciones = []): \Barryvdh\DomPDF\PDF
    {
        $datos = $this->certificadoLaboral($empleado, $opciones);
        
        return Pdf::loadView('nomina.reportes.certificado_laboral', $datos)
            ->setPaper('letter', 'portrait');
    }
    
    /**
     * Generar PDF del certificado de ingresos
     */
    public function generarPDFIngresos(Empleado $empleado, int $anio): \Barryvdh\DomPDF\PDF
    {
        $datos = $this->certificadoIngresos($empleado, $anio);
        
        return Pdf::loadView('nomina.reportes.certificado_ingresos', $datos)
            ->setPaper('letter', 'portrait');
    }
    
    /**
     * Convertir número a texto (español)
     */
    protected function numeroATexto(float $numero): string
    {
        // Implementación básica - debería usar una librería como NumberFormatter
        $formatter = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
        return ucfirst($formatter->format($numero));
    }
    
    /**
     * Nombre del mes en español
     */
    protected function nombreMes(int $mes): string
    {
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo',
            4 => 'abril', 5 => 'mayo', 6 => 'junio',
            7 => 'julio', 8 => 'agosto', 9 => 'septiembre',
            10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        
        return $meses[$mes];
    }
    
    /**
     * Texto del tipo de contrato
     */
    protected function tipoContratoTexto(string $tipo): string
    {
        return match($tipo) {
            'indefinido' => 'Contrato a Término Indefinido',
            'fijo' => 'Contrato a Término Fijo',
            'obra_labor' => 'Contrato por Obra o Labor',
            'prestacion_servicios' => 'Contrato de Prestación de Servicios',
            default => $tipo,
        };
    }
    
    /**
     * Calcular tiempo de servicio en texto
     */
    protected function tiempoServicioTexto(Carbon $fechaIngreso): string
    {
        $diff = $fechaIngreso->diff(now());
        
        $partes = [];
        
        if ($diff->y > 0) {
            $partes[] = $diff->y . ' año' . ($diff->y > 1 ? 's' : '');
        }
        
        if ($diff->m > 0) {
            $partes[] = $diff->m . ' mes' . ($diff->m > 1 ? 'es' : '');
        }
        
        if ($diff->d > 0) {
            $partes[] = $diff->d . ' día' . ($diff->d > 1 ? 's' : '');
        }
        
        return implode(', ', $partes);
    }
}