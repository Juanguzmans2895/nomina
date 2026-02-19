<?php

namespace App\Modules\Nomina\Services\Reportes;

use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Models\NominaDetalle;
use Carbon\Carbon;

class PILAService
{
    protected $tiposDocumento = [
        'CC' => '13',
        'CE' => '22',
        'TI' => '11',
        'PA' => '41',
        'NIT' => '31',
    ];
    
    protected $tiposCotizante = [
        '01' => 'Dependiente',
        '02' => 'Independiente',
        '03' => 'Aprendiz SENA',
        '16' => 'Independiente con descuento del 40%',
    ];
    
    /**
     * Generar archivo plano PILA tipo 2
     */
    public function generarArchivoPILA(Nomina $nomina): string
    {
        $lineas = [];
        
        // Registro tipo 1: Encabezado
        $lineas[] = $this->generarRegistroEncabezado($nomina);
        
        // Registro tipo 2: Detalle por cada empleado
        $detalles = $nomina->detalles()->with('empleado')->get();
        
        foreach ($detalles as $detalle) {
            $lineas[] = $this->generarRegistroDetalle($detalle, $nomina);
        }
        
        return implode("\r\n", $lineas);
    }
    
    /**
     * Generar registro de encabezado (Tipo 1)
     */
    protected function generarRegistroEncabezado(Nomina $nomina): string
    {
        $nit = $this->limpiarNIT(config('nomina.empresa.nit'));
        $periodo = $nomina->fecha_inicio->format('Y-m');
        
        $campos = [
            '1',  // Tipo de registro
            str_pad($nit, 16, '0', STR_PAD_LEFT),  // NIT del aportante
            str_pad($periodo, 7),  // Período de cotización (AAAA-MM)
            str_pad($nomina->numero_empleados, 5, '0', STR_PAD_LEFT),  // Cantidad de cotizantes
            str_pad('', 200),  // Espacios en blanco
        ];
        
        return implode('', $campos);
    }
    
    /**
     * Generar registro de detalle (Tipo 2)
     */
    protected function generarRegistroDetalle(NominaDetalle $detalle, Nomina $nomina): string
    {
        $empleado = $detalle->empleado;
        
        // Tipo de documento
        $tipoDocumento = $this->tiposDocumento[$empleado->tipo_documento] ?? '13';
        
        // Número de documento
        $numeroDocumento = str_pad($empleado->numero_documento, 16, '0', STR_PAD_LEFT);
        
        // Tipo de cotizante
        $tipoCotizante = '01'; // Dependiente por defecto
        
        // Subtipo de cotizante
        $subtipoCotizante = '00';
        
        // Apellidos y nombres
        $primerApellido = str_pad(substr($empleado->primer_apellido, 0, 20), 20);
        $segundoApellido = str_pad(substr($empleado->segundo_apellido ?? '', 0, 30), 30);
        $primerNombre = str_pad(substr($empleado->primer_nombre, 0, 20), 20);
        $segundoNombre = str_pad(substr($empleado->segundo_nombre ?? '', 0, 30), 30);
        
        // IBC (Ingreso Base de Cotización)
        $ibc = str_pad(number_format($detalle->base_seguridad_social, 0, '', ''), 9, '0', STR_PAD_LEFT);
        
        // Código EPS
        $codigoEPS = str_pad($empleado->eps_codigo ?? '', 6);
        
        // Código AFP
        $codigoAFP = str_pad($empleado->pension_codigo ?? '', 6);
        
        // Código ARL
        $codigoARL = str_pad($empleado->arl_codigo ?? '', 6);
        
        // Código CCF
        $codigoCCF = str_pad($empleado->caja_codigo ?? '', 6);
        
        // Días cotizados
        $diasCotizados = str_pad($detalle->dias_trabajados, 2, '0', STR_PAD_LEFT);
        
        // Salud
        $aporteEmpleadoSalud = str_pad(number_format($detalle->aporte_salud_empleado, 0, '', ''), 9, '0', STR_PAD_LEFT);
        $aporteEmpleadorSalud = str_pad(number_format($detalle->aporte_salud_empleador, 0, '', ''), 9, '0', STR_PAD_LEFT);
        
        // Pensión
        $aporteEmpleadoPension = str_pad(number_format($detalle->aporte_pension_empleado, 0, '', ''), 9, '0', STR_PAD_LEFT);
        $aporteEmpleadorPension = str_pad(number_format($detalle->aporte_pension_empleador, 0, '', ''), 9, '0', STR_PAD_LEFT);
        
        // FSP
        $aporteFSP = str_pad(number_format($detalle->fondo_solidaridad_empleado, 0, '', ''), 9, '0', STR_PAD_LEFT);
        
        // ARL
        $aporteARL = str_pad(number_format($detalle->aporte_arl_empleador, 0, '', ''), 9, '0', STR_PAD_LEFT);
        
        // CCF
        $aporteCCF = str_pad(number_format($detalle->aporte_caja, 0, '', ''), 9, '0', STR_PAD_LEFT);
        
        $campos = [
            '2',  // Tipo de registro
            $tipoDocumento,
            $numeroDocumento,
            $tipoCotizante,
            $subtipoCotizante,
            $primerApellido,
            $segundoApellido,
            $primerNombre,
            $segundoNombre,
            $ibc,
            $diasCotizados,
            $codigoEPS,
            $aporteEmpleadoSalud,
            $aporteEmpleadorSalud,
            $codigoAFP,
            $aporteEmpleadoPension,
            $aporteEmpleadorPension,
            $aporteFSP,
            $codigoARL,
            $aporteARL,
            $codigoCCF,
            $aporteCCF,
        ];
        
        return implode('', $campos);
    }
    
    /**
     * Generar planilla integrada (formato Excel)
     */
    public function generarPlanillaIntegrada(Nomina $nomina): array
    {
        $detalles = $nomina->detalles()->with('empleado')->get();
        
        $planilla = [];
        
        foreach ($detalles as $detalle) {
            $empleado = $detalle->empleado;
            
            $planilla[] = [
                'TIPO_DOC' => $empleado->tipo_documento,
                'NUM_DOC' => $empleado->numero_documento,
                'PRIMER_APELLIDO' => $empleado->primer_apellido,
                'SEGUNDO_APELLIDO' => $empleado->segundo_apellido,
                'PRIMER_NOMBRE' => $empleado->primer_nombre,
                'SEGUNDO_NOMBRE' => $empleado->segundo_nombre,
                'IBC' => $detalle->base_seguridad_social,
                'DIAS' => $detalle->dias_trabajados,
                
                // Salud
                'EPS' => $empleado->eps,
                'APORTE_SALUD_EMPLEADO' => $detalle->aporte_salud_empleado,
                'APORTE_SALUD_EMPLEADOR' => $detalle->aporte_salud_empleador,
                'TOTAL_SALUD' => $detalle->aporte_salud_empleado + $detalle->aporte_salud_empleador,
                
                // Pensión
                'PENSION' => $empleado->fondo_pension,
                'APORTE_PENSION_EMPLEADO' => $detalle->aporte_pension_empleado,
                'APORTE_PENSION_EMPLEADOR' => $detalle->aporte_pension_empleador,
                'FSP' => $detalle->fondo_solidaridad_empleado,
                'TOTAL_PENSION' => $detalle->aporte_pension_empleado + $detalle->aporte_pension_empleador + $detalle->fondo_solidaridad_empleado,
                
                // ARL
                'ARL' => $empleado->arl,
                'APORTE_ARL' => $detalle->aporte_arl_empleador,
                
                // CCF
                'CCF' => $empleado->caja_compensacion,
                'APORTE_CCF' => $detalle->aporte_caja,
                
                // Parafiscales
                'APORTE_SENA' => $detalle->aporte_sena,
                'APORTE_ICBF' => $detalle->aporte_icbf,
            ];
        }
        
        return $planilla;
    }
    
    /**
     * Validar archivo PILA generado
     */
    public function validarArchivoPILA(string $contenido): array
    {
        $lineas = explode("\r\n", $contenido);
        $errores = [];
        
        // Validar encabezado
        if (!isset($lineas[0]) || substr($lineas[0], 0, 1) !== '1') {
            $errores[] = 'No se encontró registro de encabezado válido';
        }
        
        // Validar cantidad de registros
        $cantidadEsperada = substr($lineas[0], 17, 5);
        $cantidadReal = count($lineas) - 1;
        
        if ((int)$cantidadEsperada !== $cantidadReal) {
            $errores[] = "Cantidad de registros no coincide. Esperados: {$cantidadEsperada}, Encontrados: {$cantidadReal}";
        }
        
        return [
            'valido' => empty($errores),
            'errores' => $errores,
            'total_registros' => count($lineas),
        ];
    }
    
    /**
     * Limpiar NIT
     */
    protected function limpiarNIT(string $nit): string
    {
        return preg_replace('/[^0-9]/', '', $nit);
    }
    
    /**
     * Generar nombre de archivo PILA
     */
    public function generarNombreArchivo(Nomina $nomina): string
    {
        $nit = $this->limpiarNIT(config('nomina.empresa.nit'));
        $periodo = $nomina->fecha_inicio->format('Ym');
        
        return "PILA_{$nit}_{$periodo}.txt";
    }
}