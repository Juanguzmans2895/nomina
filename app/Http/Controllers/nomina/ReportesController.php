<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Models\DetalleNomina;
use App\Modules\Nomina\Models\Empleado;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportesController extends Controller
{
    /**
     * Generador de reportes
     */
    public function index(Request $request)
    {
        $tipo = $request->get('tipo');
        
        // Obtener nóminas disponibles
        $nominas = Nomina::with('tipo', 'periodo')
            ->where('estado', '!=', 'borrador')
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();
        
        // Obtener empleados activos
        $empleados = Empleado::where('estado', 'activo')
            ->orderBy('primer_apellido')
            ->orderBy('primer_nombre')
            ->get();
        
        return view('nomina.livewire.reportes.generador-reportes', compact('tipo', 'nominas', 'empleados'));
    }
    
    /**
     * Desprendible individual
     */
    public function desprendible($id)
    {
        $detalle = DetalleNomina::with(['nomina.periodo', 'nomina.tipo', 'empleado'])
            ->findOrFail($id);
        
        // Datos de la empresa
        $empresa = [
            'nombre' => config('nomina.empresa.razon_social', config('app.name', 'MI EMPRESA S.A.S.')),
            'nit' => config('nomina.empresa.nit', '900.000.000-1'),
            'direccion' => config('nomina.empresa.direccion', 'Calle 123 #45-67'),
            'ciudad' => config('nomina.empresa.ciudad', 'Bogotá D.C.'),
        ];
        
        // Datos del empleado
        $empleado = [
            'nombre' => $detalle->empleado->nombre_completo,
            'documento' => $detalle->empleado->tipo_documento . ' ' . $detalle->empleado->numero_documento,
            'cargo' => $detalle->empleado->cargo ?? 'N/A',
            'dependencia' => $detalle->empleado->dependencia ?? null,
        ];
        
        // Datos de la nómina
        $nomina = [
            'numero' => $detalle->nomina->numero_nomina,
            'periodo' => $detalle->nomina->periodo->nombre ?? 'N/A',
            'fecha_pago' => $detalle->nomina->fecha_pago ? $detalle->nomina->fecha_pago->format('d/m/Y') : 'N/A',
        ];
        
        // Días trabajados
        $dias_trabajados = $detalle->dias_trabajados ?? 30;
        
        // Salario básico y auxilio de transporte
        $salario_basico = $detalle->salario_basico ?? 0;
        $auxilio_transporte = $detalle->auxilio_transporte ?? 0;
        
        // DEVENGADOS
        $devengados = [
            ['concepto' => 'Salario Básico', 'valor' => $salario_basico],
        ];
        
        if ($auxilio_transporte > 0) {
            $devengados[] = ['concepto' => 'Auxilio de Transporte', 'valor' => $auxilio_transporte];
        }
        
        $total_devengado = $detalle->total_devengado ?? ($salario_basico + $auxilio_transporte);
        
        // DEDUCCIONES
        $salud_empleado = $detalle->salud_empleado ?? ($salario_basico * 0.04);
        $pension_empleado = $detalle->pension_empleado ?? ($salario_basico * 0.04);
        $retencion = $detalle->retencion_fuente ?? 0;
        
        $deducciones = [
            ['concepto' => 'Salud (4%)', 'valor' => $salud_empleado],
            ['concepto' => 'Pensión (4%)', 'valor' => $pension_empleado],
        ];
        
        if ($retencion > 0) {
            $deducciones[] = ['concepto' => 'Retención en la Fuente', 'valor' => $retencion];
        }
        
        $total_deducciones = $detalle->total_deducciones ?? ($salud_empleado + $pension_empleado + $retencion);
        
        // NETO A PAGAR
        $neto_pagar = $detalle->total_neto ?? ($total_devengado - $total_deducciones);
        
        // SEGURIDAD SOCIAL EMPLEADOR
        $salud_empleador = $detalle->salud_empleador ?? ($salario_basico * 0.085);
        $pension_empleador = $detalle->pension_empleador ?? ($salario_basico * 0.12);
        $arl_empleador = $detalle->arl_empleador ?? ($salario_basico * 0.00522);
        
        $seguridad_social_empleador = [
            ['concepto' => 'Salud Empleador (8.5%)', 'valor' => $salud_empleador],
            ['concepto' => 'Pensión Empleador (12%)', 'valor' => $pension_empleador],
            ['concepto' => 'ARL (0.522%)', 'valor' => $arl_empleador],
        ];
        
        // PARAFISCALES
        $base_parafiscales = $salario_basico;
        $sena = $base_parafiscales * 0.02;
        $icbf = $base_parafiscales * 0.03;
        $caja = $base_parafiscales * 0.04;
        
        $parafiscales = [
            ['concepto' => 'SENA (2%)', 'valor' => $sena],
            ['concepto' => 'ICBF (3%)', 'valor' => $icbf],
            ['concepto' => 'Caja de Compensación (4%)', 'valor' => $caja],
        ];
        
        // PROVISIONES
        $base_provision = $salario_basico + $auxilio_transporte;
        $cesantias = $base_provision * 0.0833;
        $intereses_cesantias = $cesantias * 0.12 / 12;
        $prima = $base_provision * 0.0833;
        $vacaciones = $salario_basico * 0.0417;
        
        $provisiones = [
            ['concepto' => 'Cesantías (8.33%)', 'valor' => $cesantias],
            ['concepto' => 'Intereses sobre Cesantías (1%)', 'valor' => $intereses_cesantias],
            ['concepto' => 'Prima de Servicios (8.33%)', 'valor' => $prima],
            ['concepto' => 'Vacaciones (4.17%)', 'valor' => $vacaciones],
        ];
        
        // COSTO TOTAL EMPLEADOR
        $costo_empleador = $neto_pagar + 
                        $salud_empleador + $pension_empleador + $arl_empleador +
                        $sena + $icbf + $caja +
                        $cesantias + $intereses_cesantias + $prima + $vacaciones;
        
        // INFORMACIÓN BANCARIA
        $banco = [
            'entidad' => $detalle->empleado->banco ?? 'N/A',
            'tipo_cuenta' => $detalle->empleado->tipo_cuenta ?? 'N/A',
            'numero_cuenta' => $detalle->empleado->numero_cuenta ?? 'N/A',
        ];
        
        $data = [
            'empresa' => $empresa,
            'empleado' => $empleado,
            'nomina' => $nomina,
            'dias_trabajados' => $dias_trabajados,
            'devengados' => $devengados,
            'total_devengado' => $total_devengado,
            'deducciones' => $deducciones,
            'total_deducciones' => $total_deducciones,
            'neto_pagar' => $neto_pagar,
            'seguridad_social_empleador' => $seguridad_social_empleador,
            'parafiscales' => $parafiscales,
            'provisiones' => $provisiones,
            'costo_empleador' => $costo_empleador,
            'banco' => $banco,
        ];
        
        $pdf = Pdf::loadView('nomina.reportes.desprendible', $data);
        
        return $pdf->stream('desprendible-' . $detalle->empleado->numero_documento . '.pdf');
    }
    
    /**
     * Desprendibles masivos
     */
    public function desprendiblesMasivo(Nomina $nomina)
    {
        $nomina->load(['detallesNomina.empleado', 'periodo', 'tipo']);
        
        if ($nomina->detallesNomina->isEmpty()) {
            $detalles = DetalleNomina::where('nomina_id', $nomina->id)
                ->with('empleado')
                ->get();
            
            if ($detalles->isEmpty()) {
                return redirect()->back()->with('error', 
                    'Esta nómina no tiene empleados asociados.');
            }
            
            $nomina->setRelation('detallesNomina', $detalles);
        }
        
        $data = [
            'nomina' => $nomina,
            'detalles' => $nomina->detallesNomina,
        ];
        
        $pdf = Pdf::loadView('nomina.reportes.desprendibles-masivo', $data);
        
        return $pdf->stream('desprendibles-' . $nomina->numero_nomina . '.pdf');
    }
    
    /**
     * Certificado laboral
     */
    public function certificadoLaboral(Empleado $empleado)
    {
        $empresa = [
            'nombre' => config('app.name', 'Mi Empresa'),
            'nit' => '900.123.456-7',
            'direccion' => 'Calle 123 # 45-67',
            'ciudad' => 'Bogotá D.C.',
            'telefono' => '(601) 234-5678',
            'representante' => 'Juan Pérez',
            'cargo_representante' => 'Gerente General',
        ];
        
        $empleadoData = [
            'nombre' => $empleado->nombre_completo,
            'documento' => $empleado->tipo_documento . ' ' . $empleado->numero_documento,
            'cargo' => $empleado->cargo ?? 'N/A',
            'dependencia' => $empleado->dependencia ?? null,
            'tipo_contrato' => $empleado->tipo_contrato ?? 'TÉRMINO INDEFINIDO',
            'fecha_ingreso' => $empleado->fecha_ingreso,
            'fecha_ingreso_texto' => $empleado->fecha_ingreso->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
            'salario_basico' => $empleado->salario_basico,
            'salario_texto' => $this->numeroALetras($empleado->salario_basico),
        ];
        
        $diasServicio = $empleado->fecha_ingreso->diffInDays(now());
        $anios = floor($diasServicio / 365);
        $meses = floor(($diasServicio % 365) / 30);
        
        $tiempo_servicio = [
            'dias' => $diasServicio,
            'anios' => $anios,
            'meses' => $meses,
            'texto' => $this->formatearTiempoServicio($anios, $meses),
        ];
        
        $data = [
            'empresa' => $empresa,
            'empleado' => $empleadoData,
            'tiempo_servicio' => $tiempo_servicio,
            'tipo' => 'vigencia',
            'motivo' => 'A petición del interesado',
            'incluir_salario' => true,
            'incluir_funciones' => false,
            'fecha_expedicion' => now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
            'fecha_expedicion_texto' => now()->locale('es')->isoFormat('D [días del mes de] MMMM [de] YYYY'),
        ];
        
        $pdf = Pdf::loadView('nomina.reportes.certificado-laboral', $data);
        
        return $pdf->stream('certificado-laboral-' . $empleado->numero_documento . '.pdf');
    }
    
    /**
     * Certificado de ingresos
     */
    public function certificadoIngresos(Empleado $empleado, $anio)
    {
        $detalles = DetalleNomina::whereHas('nomina', function($q) use ($anio) {
                $q->whereYear('fecha_inicio', $anio);
            })
            ->where('empleado_id', $empleado->id)
            ->with('nomina')
            ->get();
        
        $totalDevengado = $detalles->sum('total_devengado');
        $totalDeducciones = $detalles->sum('total_deducciones');
        $totalRetencion = $detalles->sum('retencion_fuente') ?? 0;
        $totalNeto = $detalles->sum('total_neto');
        
        $totalSalud = $detalles->sum('salud_empleado') ?? ($totalDevengado * 0.04);
        $totalPension = $detalles->sum('pension_empleado') ?? ($totalDevengado * 0.04);
        
        $empresa = [
            'nombre' => config('app.name', 'MI EMPRESA S.A.S.'),
            'nit' => config('nomina.empresa.nit', '900.000.000-1'),
            'direccion' => config('nomina.empresa.direccion', 'Calle 123 #45-67'),
            'ciudad' => config('nomina.empresa.ciudad', 'Bogotá D.C.'),
            'telefono' => config('nomina.empresa.telefono', '(601) 234-5678'),
        ];
        
        $periodo = [
            'anio' => $anio,
            'texto' => 'Enero 1 a Diciembre 31 de ' . $anio,
        ];
        
        $empleadoData = [
            'nombre' => $empleado->nombre_completo,
            'documento' => $empleado->tipo_documento . ' ' . $empleado->numero_documento,
            'direccion' => $empleado->direccion ?? 'No registrada',
            'cargo' => $empleado->cargo ?? 'N/A',
        ];
        
        $ingresos = [
            'total_devengado' => $totalDevengado,
        ];
        
        $deducciones = [
            'salud' => $totalSalud,
            'pension' => $totalPension,
            'retencion' => $totalRetencion,
            'total' => $totalDeducciones,
        ];
        
        $neto = $totalNeto;
        $fecha_expedicion = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        
        $data = [
            'empresa' => $empresa,
            'periodo' => $periodo,
            'empleado' => $empleadoData,
            'ingresos' => $ingresos,
            'deducciones' => $deducciones,
            'neto' => $neto,
            'fecha_expedicion' => $fecha_expedicion,
            'detalles' => $detalles,
        ];
        
        $pdf = Pdf::loadView('nomina.reportes.certificado-ingresos', $data);
        
        return $pdf->stream('certificado-ingresos-' . $empleado->numero_documento . '-' . $anio . '.pdf');
    }
    
    /**
     * Certificado de cesantías
     */
    public function certificadoCesantias(Empleado $empleado)
    {
        $provision = $empleado->provision ?? null;
        
        $saldoCesantias = $provision ? $provision->saldo_cesantias : 0;
        $saldoIntereses = $provision ? $provision->saldo_intereses : 0;
        
        $data = [
            'empleado' => $empleado,
            'saldo_cesantias' => $saldoCesantias,
            'saldo_intereses' => $saldoIntereses,
            'saldo_total' => $saldoCesantias + $saldoIntereses,
        ];
        
        $pdf = Pdf::loadView('nomina.reportes.certificado-cesantias', $data);
        
        return $pdf->stream('certificado-cesantias-' . $empleado->numero_documento . '.pdf');
    }
    
    /**
     * Consolidado de nómina
     */
    public function consolidado(Nomina $nomina)
    {
        $nomina->load('detallesNomina.empleado', 'periodo', 'tipo');
        
        if ($nomina->detallesNomina->isEmpty()) {
            return redirect()->back()->with('error', 'Esta nómina no tiene empleados asociados');
        }
        
        $totales = [
            'total_devengado' => 0,
            'total_deducciones' => 0,
            'total_neto' => 0,
            'total_salud_empleado' => 0,
            'total_pension_empleado' => 0,
            'total_salud_empleador' => 0,
            'total_pension_empleador' => 0,
            'total_arl' => 0,
            'total_sena' => 0,
            'total_icbf' => 0,
            'total_caja' => 0,
            'total_parafiscales' => 0,
            'total_cesantias' => 0,
            'total_intereses_cesantias' => 0,
            'total_prima' => 0,
            'total_vacaciones' => 0,
            'total_provisiones' => 0,
            'costo_total_empleador' => 0,
        ];
        
        $detallesArray = [];
        
        foreach ($nomina->detallesNomina as $detalle) {
            $salarioBasico = $detalle->salario_basico;
            $diasTrabajados = $detalle->dias_trabajados ?? 30;
            $auxilioTransporte = $detalle->auxilio_transporte ?? 0;
            
            $devengado = $salarioBasico + $auxilioTransporte;
            $saludEmpleado = $salarioBasico * 0.04;
            $pensionEmpleado = $salarioBasico * 0.04;
            $deducciones = $saludEmpleado + $pensionEmpleado;
            $neto = $devengado - $deducciones;
            
            $saludEmpleador = $salarioBasico * 0.085;
            $pensionEmpleador = $salarioBasico * 0.12;
            $arl = $salarioBasico * 0.00522;
            
            $sena = $salarioBasico * 0.02;
            $icbf = $salarioBasico * 0.03;
            $caja = $salarioBasico * 0.04;
            $parafiscales = $sena + $icbf + $caja;
            
            $baseProvision = $salarioBasico + $auxilioTransporte;
            $cesantias = $baseProvision * 0.0833;
            $interesesCesantias = $cesantias * 0.12 / 12;
            $prima = $baseProvision * 0.0833;
            $vacaciones = $salarioBasico * 0.0417;
            $provisiones = $cesantias + $interesesCesantias + $prima + $vacaciones;
            
            $costoEmpleador = $neto + $saludEmpleador + $pensionEmpleador + $arl + $parafiscales + $provisiones;
            
            $detallesArray[] = (object)[
                'empleado' => $detalle->empleado,
                'salario_basico' => $salarioBasico,
                'dias_trabajados' => $diasTrabajados,
                'auxilio_transporte' => $auxilioTransporte,
                'total_devengado' => $devengado,
                'salud_empleado' => $saludEmpleado,
                'pension_empleado' => $pensionEmpleado,
                'total_deducciones' => $deducciones,
                'total_neto' => $neto,
                'salud_empleador' => $saludEmpleador,
                'pension_empleador' => $pensionEmpleador,
                'arl_empleador' => $arl,
                'sena' => $sena,
                'icbf' => $icbf,
                'caja' => $caja,
                'total_parafiscales' => $parafiscales,
                'cesantias' => $cesantias,
                'intereses_cesantias' => $interesesCesantias,
                'prima' => $prima,
                'vacaciones' => $vacaciones,
                'total_provisiones' => $provisiones,
                'costo_total_empleador' => $costoEmpleador,
            ];
            
            $totales['total_devengado'] += $devengado;
            $totales['total_deducciones'] += $deducciones;
            $totales['total_neto'] += $neto;
            $totales['total_salud_empleado'] += $saludEmpleado;
            $totales['total_pension_empleado'] += $pensionEmpleado;
            $totales['total_salud_empleador'] += $saludEmpleador;
            $totales['total_pension_empleador'] += $pensionEmpleador;
            $totales['total_arl'] += $arl;
            $totales['total_sena'] += $sena;
            $totales['total_icbf'] += $icbf;
            $totales['total_caja'] += $caja;
            $totales['total_parafiscales'] += $parafiscales;
            $totales['total_cesantias'] += $cesantias;
            $totales['total_intereses_cesantias'] += $interesesCesantias;
            $totales['total_prima'] += $prima;
            $totales['total_vacaciones'] += $vacaciones;
            $totales['total_provisiones'] += $provisiones;
            $totales['costo_total_empleador'] += $costoEmpleador;
        }
        
        $data = [
            'nomina' => $nomina,
            'detalles' => $detallesArray,
            'totales' => $totales,
            'numero_empleados' => count($detallesArray),
        ];
        
        $pdf = Pdf::loadView('nomina.reportes.consolidado', $data);
        
        return $pdf->stream('consolidado-' . $nomina->numero_nomina . '.pdf');
    }
    
    /**
     * Consolidado de seguridad social
     */
    public function consolidadoSeguridadSocial(Nomina $nomina)
    {
        $nomina->load('detallesNomina.empleado');
        
        if ($nomina->detallesNomina->isEmpty()) {
            return redirect()->back()->with('error', 'Esta nómina no tiene empleados asociados');
        }
        
        $detallesArray = [];
        
        $totales = [
            'salud_empleado' => 0,
            'salud_empleador' => 0,
            'salud_total' => 0,
            'pension_empleado' => 0,
            'pension_empleador' => 0,
            'pension_total' => 0,
            'arl' => 0,
            'fsp' => 0,
            'sena' => 0,
            'icbf' => 0,
            'caja' => 0,
            'parafiscales' => 0,
            'seguridad_social_total' => 0,
            'gran_total' => 0,
        ];
        
        foreach ($nomina->detallesNomina as $detalle) {
            $ibc = $detalle->salario_basico;
            
            $saludEmp = $ibc * 0.04;
            $saludEmpr = $ibc * 0.085;
            $pensionEmp = $ibc * 0.04;
            $pensionEmpr = $ibc * 0.12;
            $arl = $ibc * 0.00522;
            $sena = $ibc * 0.02;
            $icbf = $ibc * 0.03;
            $caja = $ibc * 0.04;
            
            $detallesArray[] = (object)[
                'empleado' => $detalle->empleado,
                'salario_basico' => $ibc,
                'salud_empleado' => $saludEmp,
                'salud_empleador' => $saludEmpr,
                'pension_empleado' => $pensionEmp,
                'pension_empleador' => $pensionEmpr,
                'arl_empleador' => $arl,
                'sena' => $sena,
                'icbf' => $icbf,
                'caja' => $caja,
                'total_parafiscales' => $sena + $icbf + $caja,
            ];
            
            $totales['salud_empleado'] += $saludEmp;
            $totales['salud_empleador'] += $saludEmpr;
            $totales['pension_empleado'] += $pensionEmp;
            $totales['pension_empleador'] += $pensionEmpr;
            $totales['arl'] += $arl;
            $totales['sena'] += $sena;
            $totales['icbf'] += $icbf;
            $totales['caja'] += $caja;
        }
        
        $totales['salud_total'] = $totales['salud_empleado'] + $totales['salud_empleador'];
        $totales['pension_total'] = $totales['pension_empleado'] + $totales['pension_empleador'];
        $totales['parafiscales'] = $totales['sena'] + $totales['icbf'] + $totales['caja'];
        $totales['seguridad_social_total'] = $totales['salud_total'] + $totales['pension_total'] + $totales['arl'];
        $totales['gran_total'] = $totales['seguridad_social_total'] + $totales['parafiscales'];
        
        // Consolidado por entidades
        $consolidadoEntidades = [
            [
                'tipo' => 'SALUD',
                'nombre' => 'EPS - Entidad Promotora de Salud',
                'cantidad_empleados' => count($detallesArray),
                'aporte_empleado' => $totales['salud_empleado'],
                'aporte_empleador' => $totales['salud_empleador'],
                'total' => $totales['salud_total'],
            ],
            [
                'tipo' => 'PENSIÓN',
                'nombre' => 'Fondo de Pensiones',
                'cantidad_empleados' => count($detallesArray),
                'aporte_empleado' => $totales['pension_empleado'],
                'aporte_empleador' => $totales['pension_empleador'],
                'total' => $totales['pension_total'],
            ],
            [
                'tipo' => 'ARL',
                'nombre' => 'Administradora de Riesgos Laborales',
                'cantidad_empleados' => count($detallesArray),
                'aporte_empleado' => 0,
                'aporte_empleador' => $totales['arl'],
                'total' => $totales['arl'],
            ],
            [
                'tipo' => 'SENA',
                'nombre' => 'Servicio Nacional de Aprendizaje',
                'cantidad_empleados' => count($detallesArray),
                'aporte_empleado' => 0,
                'aporte_empleador' => $totales['sena'],
                'total' => $totales['sena'],
            ],
            [
                'tipo' => 'ICBF',
                'nombre' => 'Instituto Colombiano de Bienestar Familiar',
                'cantidad_empleados' => count($detallesArray),
                'aporte_empleado' => 0,
                'aporte_empleador' => $totales['icbf'],
                'total' => $totales['icbf'],
            ],
            [
                'tipo' => 'CAJA',
                'nombre' => 'Caja de Compensación Familiar',
                'cantidad_empleados' => count($detallesArray),
                'aporte_empleado' => 0,
                'aporte_empleador' => $totales['caja'],
                'total' => $totales['caja'],
            ],
        ];
        
        $pdf = Pdf::loadView('nomina.reportes.consolidado-seguridad-social', [
            'nomina' => $nomina,
            'detalles' => $detallesArray,
            'totales' => $totales,
            'consolidadoEntidades' => $consolidadoEntidades,
        ]);
        
        return $pdf->stream('consolidado-ss-' . $nomina->numero_nomina . '.pdf');
    }
    
    /**
     * Reporte ejecutivo
     */
    public function reporteEjecutivo($periodo)
    {
        return redirect()->back()->with('info', 'Función en desarrollo');
    }
    
    /**
     * Archivo PILA
     */
    public function archivoPILA(Nomina $nomina)
    {
        return redirect()->back()->with('info', 'Función de archivo PILA en desarrollo');
    }
    
    /**
     * Exportar nómina a Excel
     */
    public function exportarNominaExcel(Nomina $nomina)
    {
        return redirect()->back()->with('info', 'Función de exportación a Excel en desarrollo');
    }
    
    /**
     * Exportar provisiones a Excel
     */
    public function exportarProvisionesExcel()
    {
        return redirect()->back()->with('info', 'Función de exportación de provisiones en desarrollo');
    }
    
    /**
     * Helpers
     */
    private function numeroALetras($numero)
    {
        return 'VALOR EN LETRAS';
    }
    
    private function formatearTiempoServicio($anios, $meses)
    {
        $texto = '';
        
        if ($anios > 0) {
            $texto .= $anios . ($anios == 1 ? ' año' : ' años');
        }
        
        if ($meses > 0) {
            if ($texto) $texto .= ' y ';
            $texto .= $meses . ($meses == 1 ? ' mes' : ' meses');
        }
        
        return $texto ?: '0 meses';
    }
}