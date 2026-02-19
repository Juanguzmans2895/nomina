<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Models\Contrato;
use App\Modules\Nomina\Models\Provision;
use App\Modules\Nomina\Models\DetalleNomina;
use Carbon\Carbon;

class NominaController extends Controller
{
    /**
     * Dashboard principal de nómina con métricas completas
     */
    public function dashboard()
    {
        // ══════════════════════════════════════════════════════════
        // MÉTRICAS PRINCIPALES
        // ══════════════════════════════════════════════════════════
        
        // 1. Empleados Activos
        $empleadosActivos = \App\Modules\Nomina\Models\Empleado::where('estado', 'activo')->count();
        $empleadosIndefinidos = \App\Modules\Nomina\Models\Empleado::where('estado', 'activo')
            ->where('tipo_contrato', 'indefinido')->count();
        $empleadosFijos = \App\Modules\Nomina\Models\Empleado::where('estado', 'activo')
            ->where('tipo_contrato', 'fijo')->count();

        // 2. Nómina Mes Actual
        $nominaMesActual = \App\Modules\Nomina\Models\Nomina::whereMonth('fecha_inicio', now()->month)
            ->whereYear('fecha_inicio', now()->year)
            ->orderByDesc('id')
            ->first();

        $totalNetoActual = $nominaMesActual->total_neto ?? 0;
        $estadoNominaActual = $nominaMesActual ? 
            (is_object($nominaMesActual->estado) ? $nominaMesActual->estado->value : $nominaMesActual->estado) 
            : null;

        // Calcular tendencia (comparar con mes anterior)
        $nominaMesAnterior = \App\Modules\Nomina\Models\Nomina::whereMonth('fecha_inicio', now()->subMonth()->month)
            ->whereYear('fecha_inicio', now()->subMonth()->year)
            ->orderByDesc('id')
            ->first();
        
        $tendencia = 0;
        if ($nominaMesAnterior && $nominaMesAnterior->total_neto > 0 && $totalNetoActual > 0) {
            $tendencia = round((($totalNetoActual - $nominaMesAnterior->total_neto) / $nominaMesAnterior->total_neto) * 100, 1);
        }

        // 3. Provisiones Totales
        $provisionesTotales = \App\Modules\Nomina\Models\Provision::where('tipo_provision', 'mensual')
            ->sum(\DB::raw('saldo_cesantias + saldo_intereses + saldo_prima + saldo_vacaciones'));

        $provisionesDetalle = [
            'cesantias' => \App\Modules\Nomina\Models\Provision::where('tipo_provision', 'mensual')->sum('saldo_cesantias'),
            'intereses' => \App\Modules\Nomina\Models\Provision::where('tipo_provision', 'mensual')->sum('saldo_intereses'),
            'prima' => \App\Modules\Nomina\Models\Provision::where('tipo_provision', 'mensual')->sum('saldo_prima'),
            'vacaciones' => \App\Modules\Nomina\Models\Provision::where('tipo_provision', 'mensual')->sum('saldo_vacaciones'),
        ];

        // 4. Contratos Activos
        $contratosActivos = \App\Modules\Nomina\Models\Contrato::where('estado', 'activo')->count();
        $contratosProximosVencer = \App\Modules\Nomina\Models\Contrato::where('estado', 'activo')
            ->whereDate('fecha_fin', '>=', now())
            ->whereDate('fecha_fin', '<=', now()->addDays(30))
            ->count();

        // 5. Asientos Contables
        $asientosTotal = \DB::table('asientos_contables_nomina')->count();
        $asientosContabilizados = \DB::table('asientos_contables_nomina')
            ->where('estado', 'contabilizado')->count();
        $asientosDescuadrados = \DB::table('asientos_contables_nomina')
            ->where('cuadrado', false)->count();

        // 6. Novedades Pendientes
        $novedadesPendientes = \App\Modules\Nomina\Models\NovedadNomina::where('estado', 'pendiente')->count();

        // 7. Centros de Costo
        $centrosCostoTotal = \App\Modules\Nomina\Models\CentroCosto::count();
        $centrosCostoActivos = \App\Modules\Nomina\Models\CentroCosto::where('activo', true)->count();

        // 8. Conceptos de Nómina
        $conceptosTotal = \App\Modules\Nomina\Models\ConceptoNomina::count();
        $conceptosDevengos = \App\Modules\Nomina\Models\ConceptoNomina::where('clasificacion', 'devengado')->count();
        $conceptosDeducciones = \App\Modules\Nomina\Models\ConceptoNomina::where('clasificacion', 'deducido')->count();

        // ══════════════════════════════════════════════════════════
        // GRÁFICAS
        // ══════════════════════════════════════════════════════════
        
        // Evolución de Nómina (últimos 6 meses)
        $evolucionNomina = \App\Modules\Nomina\Models\Nomina::select(
                \DB::raw('YEAR(fecha_inicio) as anio'),
                \DB::raw('MONTH(fecha_inicio) as mes'),
                \DB::raw('SUM(total_neto) as total_neto')
            )
            ->where('fecha_inicio', '>=', now()->subMonths(6))
            ->groupBy('anio', 'mes')
            ->orderBy('anio', 'asc')
            ->orderBy('mes', 'asc')
            ->get()
            ->map(function($item) {
                $meses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                return [
                    'mes_nombre' => $meses[$item->mes] . ' ' . $item->anio,
                    'total_neto' => $item->total_neto,
                ];
            });

        // Distribución por Tipo de Contrato
        $distribucionEmpleados = [
            'por_tipo_contrato' => \App\Modules\Nomina\Models\Empleado::select('tipo_contrato', \DB::raw('count(*) as total'))
                ->where('estado', 'activo')
                ->groupBy('tipo_contrato')
                ->get()
                ->map(fn($item) => [
                    'tipo_contrato' => ucfirst($item->tipo_contrato),
                    'total' => $item->total
                ])
        ];

        // ══════════════════════════════════════════════════════════
        // TABLAS
        // ══════════════════════════════════════════════════════════
        
        // Nóminas Recientes (últimas 5)
        $nominasRecientes = \App\Modules\Nomina\Models\Nomina::with('periodo')
            ->orderByDesc('id')
            ->take(5)
            ->get()
            ->map(function($nomina) {
                $estado = is_object($nomina->estado) ? $nomina->estado->value : $nomina->estado;
                return [
                    'numero' => $nomina->numero_nomina,
                    'periodo' => $nomina->periodo->nombre ?? $nomina->fecha_inicio->format('m/Y'),
                    'estado' => $estado,
                    'total_neto' => $nomina->total_neto ?? 0,
                ];
            });

        // Novedades Pendientes (detalles de las últimas 10)
        $novedadesPendientesDetalle = \App\Modules\Nomina\Models\NovedadNomina::with('empleado', 'concepto')
            ->where('estado', 'pendiente')
            ->orderByDesc('fecha_novedad')
            ->take(10)
            ->get()
            ->map(function($novedad) {
                return [
                    'empleado' => $novedad->empleado->nombre_completo ?? 'N/A',
                    'concepto' => $novedad->concepto->nombre ?? 'N/A',
                    'valor' => $novedad->valor,
                    'fecha' => $novedad->fecha_novedad->format('d/m/Y'),
                ];
            });

        // Contratos Próximos a Vencer (30 días)
        $contratosProximosVencerDetalle = \App\Modules\Nomina\Models\Contrato::where('estado', 'activo')
            ->whereDate('fecha_fin', '>=', now())
            ->whereDate('fecha_fin', '<=', now()->addDays(30))
            ->get()
            ->map(function($contrato) {
                $diasRestantes = now()->diffInDays($contrato->fecha_fin, false);
                return [
                    'numero' => $contrato->numero_contrato,
                    'contratista' => $contrato->nombre_contratista ?? 'N/A',
                    'fecha_fin' => $contrato->fecha_fin->format('d/m/Y'),
                    'dias_restantes' => max(0, $diasRestantes),
                    'saldo_pendiente' => $contrato->valor_total - ($contrato->valor_pagado ?? 0),
                ];
            });

        // ══════════════════════════════════════════════════════════
        // PREPARAR DATOS PARA LA VISTA
        // ══════════════════════════════════════════════════════════
        
        $metricas = [
            'empleados_activos' => [
                'total' => $empleadosActivos,
                'indefinidos' => $empleadosIndefinidos,
                'fijos' => $empleadosFijos,
            ],
            'nomina_mes_actual' => [
                'total_neto' => $totalNetoActual,
                'existe' => $nominaMesActual !== null,
                'estado' => $estadoNominaActual,
                'tendencia' => $tendencia,
            ],
            'provisiones_totales' => $provisionesTotales,
            'provisiones_detalle' => $provisionesDetalle,
            'contratos' => [
                'activos' => $contratosActivos,
                'proximos_vencer' => $contratosProximosVencer,
            ],
            'asientos_contables' => [
                'total' => $asientosTotal,
                'contabilizados' => $asientosContabilizados,
                'descuadrados' => $asientosDescuadrados,
            ],
            'novedades_pendientes' => $novedadesPendientes,
            'centros_costo' => [
                'total' => $centrosCostoTotal,
                'activos' => $centrosCostoActivos,
            ],
            'conceptos' => [
                'total' => $conceptosTotal,
                'devengos' => $conceptosDevengos,
                'deducciones' => $conceptosDeducciones,
            ],
        ];

        return view('nomina.livewire.dashboard-nomina', compact(
            'metricas',
            'evolucionNomina',
            'distribucionEmpleados',
            'nominasRecientes'
        ))->with([
            'novedadesPendientes' => $novedadesPendientesDetalle,
            'contratosProximosVencer' => $contratosProximosVencerDetalle,
        ]);
    }

    /**
     * Dashboard de nómina (vista alternativa)
     */
    public function dashboardNomina()
    {
        return $this->dashboard();
    }

    /**
     * Wizard de liquidación - Ver paso actual
    */
    public function liquidar(Request $request)
    {
        $step = $request->get('step', 1);
        
        // Obtener datos guardados en sesión
        $datosNomina = session('wizard_nomina', []);
        
        // Obtener tipos de nómina
        $tiposNomina = \App\Modules\Nomina\Models\TipoNomina::where('activo', true)
            ->orderBy('nombre')
            ->get();
        
        // Obtener períodos de nómina
        $periodosNomina = \App\Modules\Nomina\Models\PeriodoNomina::where('estado', 'abierto')
            ->orderBy('fecha_inicio', 'desc')
            ->get();
        
        // Obtener empleados activos
        $empleados = \App\Modules\Nomina\Models\Empleado::where('estado', 'activo')
            ->orderBy('primer_apellido')
            ->orderBy('primer_nombre')
            ->get();
        
        // Calcular preliquidación si estamos en el paso 4
        $preliquidacion = null;
        if ($step == 4 && isset($datosNomina['empleados']) && count($datosNomina['empleados']) > 0) {
            $preliquidacion = $this->calcularPreliquidacion($datosNomina);
        }
        
        return view('nomina.livewire.nomina.wizard-liquidacion', [
            'currentStep' => (int)$step,
            'tiposNomina' => $tiposNomina,
            'periodosNomina' => $periodosNomina,
            'empleados' => $empleados,
            'datosNomina' => $datosNomina,
            'preliquidacion' => $preliquidacion,
        ]);
    }

    /**
     * Guardar datos del paso actual del wizard
     */
    public function guardarPasoWizard(Request $request)
    {
        $currentStep = $request->input('current_step', 1);
        
        // Obtener datos existentes
        $datosNomina = session('wizard_nomina', []);
        
        // Guardar datos según el paso
        switch ($currentStep) {
            case 1:
                // Paso 1: Datos básicos
                $validated = $request->validate([
                    'tipo_nomina_id' => 'required|exists:tipos_nomina,id',
                    'periodo_nomina_id' => 'required|exists:periodos_nomina,id',
                    'nombre' => 'required|string|max:255',
                    'fecha_inicio' => 'required|date',
                    'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                    'fecha_pago' => 'required|date',
                    'incluir_seguridad_social' => 'nullable|boolean',
                    'incluir_parafiscales' => 'nullable|boolean',
                    'incluir_provisiones' => 'nullable|boolean',
                ]);
                
                $datosNomina = array_merge($datosNomina, $validated);
                break;
                
            case 2:
                // Paso 2: Empleados
                $validated = $request->validate([
                    'empleados' => 'required|array|min:1',
                    'empleados.*' => 'exists:empleados,id',
                ]);
                
                $datosNomina['empleados'] = $validated['empleados'];
                break;
                
            case 3:
                // Paso 3: Novedades (opcional)
                // Por ahora solo avanzamos
                break;
                
            case 4:
                // Paso 4: Preliquidación (solo revisar)
                break;
        }
        
        // Guardar en sesión
        session(['wizard_nomina' => $datosNomina]);
        
        // Redirigir al siguiente paso
        $nextStep = $currentStep + 1;
        return redirect()->route('nomina.nominas.liquidar', ['step' => $nextStep]);
    }

    /**
     * Procesar y guardar la nómina final
     */
    public function procesar(Request $request)
    {
        $datosNomina = session('wizard_nomina', []);
        
        if (empty($datosNomina) || !isset($datosNomina['empleados'])) {
            return redirect()->route('nomina.nominas.liquidar')
                ->with('error', 'No hay datos de nómina para procesar');
        }
        
        try {
            \DB::beginTransaction();
            
            // Generar número de nómina
            $ultimaNomina = \App\Modules\Nomina\Models\Nomina::orderBy('id', 'desc')->first();
            $numero = $ultimaNomina ? $ultimaNomina->id + 1 : 1;
            $numeroNomina = 'NOM-' . date('Y') . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            
            // Calcular totales
            $preliquidacion = $this->calcularPreliquidacion($datosNomina);
            
            // Crear la nómina
            $nomina = \App\Modules\Nomina\Models\Nomina::create([
                'numero_nomina' => $numeroNomina,
                'nombre' => $datosNomina['nombre'],
                'tipo_nomina_id' => $datosNomina['tipo_nomina_id'],
                'periodo_nomina_id' => $datosNomina['periodo_nomina_id'],
                'fecha_inicio' => $datosNomina['fecha_inicio'],
                'fecha_fin' => $datosNomina['fecha_fin'],
                'fecha_pago' => $datosNomina['fecha_pago'],
                'estado' => 'borrador',
                'total_devengado' => $preliquidacion['total_devengado'],
                'total_deducciones' => $preliquidacion['total_deducciones'],
                'total_neto' => $preliquidacion['total_neto'],
                'numero_empleados' => count($datosNomina['empleados']),
                'incluir_seguridad_social' => $datosNomina['incluir_seguridad_social'] ?? true,
                'incluir_parafiscales' => $datosNomina['incluir_parafiscales'] ?? true,
                'incluir_provisiones' => $datosNomina['incluir_provisiones'] ?? true,
            ]);
            
            // Crear detalles por empleado
            foreach ($preliquidacion['empleados'] as $detalleEmpleado) {
                \App\Modules\Nomina\Models\DetalleNomina::create([
                    'nomina_id' => $nomina->id,
                    'empleado_id' => $detalleEmpleado['empleado']->id,
                    'salario_basico' => $detalleEmpleado['salario_basico'],
                    'total_devengado' => $detalleEmpleado['devengado'],
                    'total_deducciones' => $detalleEmpleado['deducciones'],
                    'total_neto' => $detalleEmpleado['neto'],
                    'salud_empleado' => $detalleEmpleado['salud_empleado'],
                    'pension_empleado' => $detalleEmpleado['pension_empleado'],
                    'salud_empleador' => $detalleEmpleado['salud_empleador'],
                    'pension_empleador' => $detalleEmpleado['pension_empleador'],
                    'arl_empleador' => $detalleEmpleado['arl_empleador'],
                ]);
            }
            
            \DB::commit();
            
            // Limpiar sesión
            session()->forget('wizard_nomina');
            
            return redirect()->route('nomina.nominas.detalles', $nomina)
                ->with('success', 'Nómina creada exitosamente');
                
        } catch (\Exception $e) {
            \DB::rollBack();
            
            return redirect()->route('nomina.nominas.liquidar')
                ->with('error', 'Error al procesar la nómina: ' . $e->getMessage());
        }
    }

    /**
     * Calcular preliquidación
     */
    private function calcularPreliquidacion($datosNomina)
    {
        $empleadosIds = $datosNomina['empleados'] ?? [];
        $empleados = \App\Modules\Nomina\Models\Empleado::whereIn('id', $empleadosIds)->get();
        
        $detallesEmpleados = [];
        $totalDevengado = 0;
        $totalDeducciones = 0;
        $totalNeto = 0;
        $totalSaludEmpleado = 0;
        $totalPensionEmpleado = 0;
        $totalSaludEmpleador = 0;
        $totalPensionEmpleador = 0;
        $totalArlEmpleador = 0;
        $totalParafiscales = 0;
        $totalProvisiones = 0;
        
        foreach ($empleados as $empleado) {
            $salarioBasico = $empleado->salario_basico;
            
            // Devengado (por ahora solo salario básico)
            $devengado = $salarioBasico;
            
            // Deducciones
            $saludEmpleado = $salarioBasico * 0.04; // 4%
            $pensionEmpleado = $salarioBasico * 0.04; // 4%
            $deducciones = $saludEmpleado + $pensionEmpleado;
            
            // Neto
            $neto = $devengado - $deducciones;
            
            // Aportes empleador
            $saludEmpleador = $salarioBasico * 0.085; // 8.5%
            $pensionEmpleador = $salarioBasico * 0.12; // 12%
            $claseRiesgo = $empleado->clase_riesgo ?? 0.00522;
            $arlEmpleador = $salarioBasico * $claseRiesgo; // Variable según riesgo
            
            $aportesSeguridadSocial = $saludEmpleador + $pensionEmpleador + $arlEmpleador;
            
            // Parafiscales (9% = 4% ICBF + 3% SENA + 2% Caja)
            $parafiscales = $salarioBasico * 0.09;
            
            // Provisiones (21.67% = 8.33% cesantías + 8.33% prima + 4.17% vacaciones + 0.83% int. cesantías)
            $provisiones = $salarioBasico * 0.2167;
            
            // Costo total empleador
            $costoEmpleador = $salarioBasico + $aportesSeguridadSocial + $parafiscales + $provisiones;
            
            $detallesEmpleados[] = [
                'empleado' => $empleado,
                'salario_basico' => $salarioBasico,
                'devengado' => $devengado,
                'deducciones' => $deducciones,
                'neto' => $neto,
                'salud_empleado' => $saludEmpleado,
                'pension_empleado' => $pensionEmpleado,
                'salud_empleador' => $saludEmpleador,
                'pension_empleador' => $pensionEmpleador,
                'arl_empleador' => $arlEmpleador,
                'parafiscales' => $parafiscales,
                'provisiones' => $provisiones,
                'costo_empleador' => $costoEmpleador,
            ];
            
            $totalDevengado += $devengado;
            $totalDeducciones += $deducciones;
            $totalNeto += $neto;
            $totalSaludEmpleado += $saludEmpleado;
            $totalPensionEmpleado += $pensionEmpleado;
            $totalSaludEmpleador += $saludEmpleador;
            $totalPensionEmpleador += $pensionEmpleador;
            $totalArlEmpleador += $arlEmpleador;
            $totalParafiscales += $parafiscales;
            $totalProvisiones += $provisiones;
        }
        
        $costoTotalEmpleador = $totalDevengado + $totalSaludEmpleador + $totalPensionEmpleador + 
                            $totalArlEmpleador + $totalParafiscales + $totalProvisiones;
        
        return [
            'empleados' => $detallesEmpleados,
            'numero_empleados' => count($empleados),
            'total_devengado' => $totalDevengado,
            'total_deducciones' => $totalDeducciones,
            'total_neto' => $totalNeto,
            'total_salud_empleado' => $totalSaludEmpleado,
            'total_pension_empleado' => $totalPensionEmpleado,
            'total_salud_empleador' => $totalSaludEmpleador,
            'total_pension_empleador' => $totalPensionEmpleador,
            'total_arl_empleador' => $totalArlEmpleador,
            'total_parafiscales' => $totalParafiscales,
            'total_provisiones' => $totalProvisiones,
            'costo_total_empleador' => $costoTotalEmpleador,
        ];
    }

    /**
     * Generar número de nómina
     */
    private function generarNumeroNomina()
    {
        $anio = date('Y');
        $mes = date('m');
        $ultimaNomina = Nomina::whereYear('created_at', $anio)
            ->whereMonth('created_at', $mes)
            ->count();
        
        $consecutivo = str_pad($ultimaNomina + 1, 3, '0', STR_PAD_LEFT);
        
        return "NOM-{$anio}{$mes}-{$consecutivo}";
    }

    /**
     * Volver al paso anterior del wizard
     */
    public function volverPaso(Request $request)
    {
        $step = $request->input('step', 1);
        $previousStep = max($step - 1, 1);
        
        return redirect()->route('nomina.nominas.liquidar', ['step' => $previousStep]);
    }
    /**
     * Historial de nóminas
     */
    public function historial()
    {
        $nominas = Nomina::with(['tipo', 'periodo'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('nomina.livewire.nomina.historial-nominas', compact('nominas'));
    }

    /**
     * Detalles de una nómina
     */
    public function detalles($id)
    {
        $nomina = Nomina::with('detallesNomina.empleado')->findOrFail($id);
        return view('nomina.livewire.nomina.detalle-nomina', compact('nomina'));
    }

    /**
     * Gestión de novedades
     */
    public function novedades(Request $request)
    {
        // Obtener novedades con filtros
        $query = \App\Modules\Nomina\Models\NovedadNomina::with(['empleado', 'concepto']);
        
        // Filtro por empleado
        if ($request->filled('empleado')) {
            $query->whereHas('empleado', function($q) use ($request) {
                $q->where('primer_nombre', 'like', "%{$request->empleado}%")
                ->orWhere('primer_apellido', 'like', "%{$request->empleado}%")
                ->orWhere('numero_documento', 'like', "%{$request->empleado}%");
            });
        }
        
        // Filtro por concepto
        if ($request->filled('concepto')) {
            $query->whereHas('concepto', function($q) use ($request) {
                $q->where('codigo', $request->concepto);
            });
        }
        
        // Filtro por estado
        if ($request->filled('estado')) {
            if ($request->estado === 'procesada') {
                $query->where('procesada', true);
            } elseif ($request->estado === 'pendiente') {
                $query->where('procesada', false);
            }
        }
        
        // Filtro por período
        if ($request->filled('periodo')) {
            if ($request->periodo === 'actual') {
                $query->whereMonth('fecha_novedad', now()->month) // ✅ CAMBIAR 'fecha' a 'fecha_novedad'
                    ->whereYear('fecha_novedad', now()->year);   // ✅ CAMBIAR 'fecha' a 'fecha_novedad'
            }
        }
        
        // Ordenar y paginar
        $novedades = $query->orderBy('fecha_novedad', 'desc') // ✅ CAMBIAR 'fecha' a 'fecha_novedad'
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);
        
        return view('nomina.livewire.nomina.gestion-novedades', compact('novedades'));
    }

    /**
     * Consulta de provisiones
     */
    public function provisiones(Request $request)
    {
        // Construir query base
        $query = \App\Modules\Nomina\Models\Provision::with('empleado')
            ->where('tipo_provision', 'mensual');
        
        // Aplicar filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('empleado', function($q) use ($search) {
                $q->where('primer_nombre', 'like', "%{$search}%")
                  ->orWhere('segundo_nombre', 'like', "%{$search}%")
                  ->orWhere('primer_apellido', 'like', "%{$search}%")
                  ->orWhere('segundo_apellido', 'like', "%{$search}%")
                  ->orWhere('numero_documento', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('estado')) {
            $query->whereHas('empleado', function($q) use ($request) {
                $q->where('estado', $request->estado);
            });
        } else {
            // Por defecto, solo empleados activos
            $query->whereHas('empleado', function($q) {
                $q->where('estado', 'activo');
            });
        }
        
        // Ordenar por empleado
        $query->orderBy('empleado_id');
        
        // Obtener provisiones
        $provisiones = $query->get();
        
        // Agregar datos calculados a cada provisión
        foreach ($provisiones as $provision) {
            if ($provision->empleado) {
                $fechaIngreso = $provision->empleado->fecha_ingreso;
                $antiguedadMeses = $fechaIngreso->diffInMonths(now());
                $antiguedadAnos = floor($antiguedadMeses / 12);
                $mesesRestantes = $antiguedadMeses % 12;
                
                $provision->antiguedad_anos = $antiguedadAnos;
                $provision->antiguedad_meses = $mesesRestantes;
                $provision->salario_base = $provision->empleado->salario_basico;
            }
        }
        
        // Calcular totales
        $totales = [
            'cesantias' => $provisiones->sum('saldo_cesantias'),
            'intereses' => $provisiones->sum('saldo_intereses'),
            'prima' => $provisiones->sum('saldo_prima'),
            'vacaciones' => $provisiones->sum('saldo_vacaciones'),
            'total' => 0,
        ];
        
        $totales['total'] = $totales['cesantias'] + $totales['intereses'] + $totales['prima'] + $totales['vacaciones'];
        
        return view('nomina.livewire.provisiones.consulta-provisiones', compact('provisiones', 'totales'));
    }

    /**
     * Asientos contables
     */
    public function asientosContables(Request $request)
    {
        // Construir query
        $query = \App\Modules\Nomina\Models\AsientoContableNomina::with('nomina', 'detalles');
        
        // Aplicar filtros
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('numero_asiento', 'like', "%{$request->search}%")
                ->orWhere('descripcion', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->filled('tipo')) {
            $query->where('tipo_asiento', $request->tipo);
        }
        
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_asiento', '>=', $request->fecha_inicio);
        }
        
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_asiento', '<=', $request->fecha_fin);
        }
        
        // Ordenar
        $query->orderBy('fecha_asiento', 'desc')->orderBy('numero_asiento', 'desc');
        
        // Paginar
        $asientos = $query->paginate(15);
        
        // Calcular estadísticas
        $estadisticas = [
            'total' => \App\Modules\Nomina\Models\AsientoContableNomina::count(),
            'borrador' => \App\Modules\Nomina\Models\AsientoContableNomina::where('estado', 'borrador')->count(),
            'aprobados' => \App\Modules\Nomina\Models\AsientoContableNomina::where('estado', 'aprobado')->count(),
            'contabilizados' => \App\Modules\Nomina\Models\AsientoContableNomina::where('estado', 'contabilizado')->count(),
            'anulados' => \App\Modules\Nomina\Models\AsientoContableNomina::where('estado', 'anulado')->count(),
        ];
        
        return view('nomina.livewire.provisiones.asientos-contables', compact('asientos', 'estadisticas'));
    }

    /**
     * Exportar asientos contables a Excel
     */
    public function exportarAsientos(Request $request)
    {
        try {
            // Obtener asientos con filtros
            $query = \App\Modules\Nomina\Models\AsientoContableNomina::with('detalles', 'nomina');
            
            // Aplicar filtros si existen
            if ($request->filled('tipo')) {
                $query->where('tipo_asiento', $request->tipo);
            }
            
            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }
            
            if ($request->filled('fecha_inicio')) {
                $query->whereDate('fecha_asiento', '>=', $request->fecha_inicio);
            }
            
            if ($request->filled('fecha_fin')) {
                $query->whereDate('fecha_asiento', '<=', $request->fecha_fin);
            }
            
            $asientos = $query->orderBy('fecha_asiento', 'desc')->get();
            
            $nombreArchivo = 'asientos-contables-' . now()->format('Y-m-d') . '.xlsx';
            
            return \Excel::download(new \App\Exports\AsientosContablesExport($asientos), $nombreArchivo);
            
        } catch (\Exception $e) {
            \Log::error('Error exportando asientos contables: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar asientos: ' . $e->getMessage());
        }
    }

    /**
     * Centros de costo
     */
    public function centrosCosto()
    {
        return view('nomina.livewire.centros-costo.gestion-centros-costo');
    }

    /**
     * Gestión de contratos
     */
    public function contratos()
    {
        $contratos = Contrato::orderBy('created_at', 'desc')->paginate(15);
        return view('nomina.livewire.contratos.gestion-contratos', compact('contratos'));
    }

    /**
     * Pagos de contrato
     */
    public function pagosContrato($id)
    {
        $contrato = Contrato::findOrFail($id);
        return view('nomina.registro-pago-contrato', compact('contrato'));
    }

    /**
     * Editar nómina
     */
    public function editar($id)
    {
        $nomina = Nomina::with('detallesNomina.empleado')->findOrFail($id);
        return view('nomina.livewire.nomina.nomina-edit', compact('nomina'));
    }

    /**
     * Actualizar nómina
     */
    public function actualizar(Request $request, $id)
    {
        $nomina = \App\Modules\Nomina\Models\Nomina::findOrFail($id);
        $nomina->update($request->all());
        
        return redirect()->route('nomina.nominas.detalles', $nomina)
            ->with('success', 'Nómina actualizada exitosamente');
    }

    /**
     * Eliminar nómina
     */
    public function eliminar($id)
    {
        $nomina = \App\Modules\Nomina\Models\Nomina::findOrFail($id);
        $nomina->delete();
        
        return redirect()->route('nomina.nominas.historial')
            ->with('success', 'Nómina eliminada exitosamente');
    }

    /**
     * Aprobar nómina
     */
    public function aprobar($id)
    {
        $nomina = \App\Modules\Nomina\Models\Nomina::findOrFail($id);
        
        // Cambiar estado a aprobada
        $estadoValue = is_object($nomina->estado) ? $nomina->estado->value : $nomina->estado;
        
        if ($estadoValue === 'borrador') {
            $nomina->estado = 'aprobada';
            $nomina->fecha_aprobacion = now();
            $nomina->save();
            
            return redirect()->route('nomina.nominas.detalles', $nomina)
                ->with('success', 'Nómina aprobada exitosamente');
        }
        
        return redirect()->back()
            ->with('error', 'Solo se pueden aprobar nóminas en estado borrador');
    }

    /**
     * Marcar nómina como pagada
     */
    public function pagar(Request $request, $id)
    {
        $nomina = \App\Modules\Nomina\Models\Nomina::findOrFail($id);
        
        $estadoValue = is_object($nomina->estado) ? $nomina->estado->value : $nomina->estado;
        
        if ($estadoValue === 'aprobada') {
            $nomina->estado = 'pagada';
            $nomina->fecha_pago = $request->fecha_pago ?? now();
            $nomina->save();
            
            return redirect()->route('nomina.nominas.detalles', $nomina)
                ->with('success', 'Nómina marcada como pagada');
        }
        
        return redirect()->back()
            ->with('error', 'Solo se pueden pagar nóminas aprobadas');
    }

    /**
     * Cerrar nómina
     */
    public function cerrar($id)
    {
        $nomina = \App\Modules\Nomina\Models\Nomina::findOrFail($id);
        
        $nomina->estado = 'cerrada';
        $nomina->fecha_cierre = now();
        $nomina->save();
        
        return redirect()->route('nomina.nominas.detalles', $nomina)
            ->with('success', 'Nómina cerrada exitosamente');
    }

    /**
     * Anular nómina
     */
    public function anular(Request $request, $id)
    {
        $nomina = \App\Modules\Nomina\Models\Nomina::findOrFail($id);
        
        $nomina->estado = 'anulada';
        $nomina->motivo_anulacion = $request->motivo ?? 'Sin motivo especificado';
        $nomina->fecha_anulacion = now();
        $nomina->save();
        
        return redirect()->route('nomina.nominas.historial')
            ->with('success', 'Nómina anulada exitosamente');
    }

    /**
     * Crear centro de costo
     */
    public function crearCentroCosto()
    {
        return view('nomina.centros-costo.create');
    }

    /**
     * Guardar centro de costo
     */
    public function guardarCentroCosto(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|unique:centros_costo',
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'responsable' => 'nullable|string',
            'presupuesto' => 'nullable|numeric',
            'activo' => 'nullable|boolean',
        ]);
        
        \App\Modules\Nomina\Models\CentroCosto::create($validated);
        
        return redirect()->route('nomina.centros-costo.index')
            ->with('success', 'Centro de costo creado exitosamente');
    }

    /**
     * Crear pago de contrato
     */
    public function crearPago($contratoId)
    {
        $contrato = \App\Modules\Nomina\Models\Contrato::findOrFail($contratoId);
        return view('nomina.contratos.pagos.create', compact('contrato'));
    }

    /**
     * Guardar pago de contrato
     */
    public function guardarPago(Request $request, $contratoId)
    {
        $validated = $request->validate([
            'numero_pago' => 'required|string',
            'fecha_pago' => 'required|date',
            'valor_bruto' => 'required|numeric',
            'retencion_fuente' => 'nullable|numeric',
            'reteica' => 'nullable|numeric',
            'reteiva' => 'nullable|numeric',
            'otras_deducciones' => 'nullable|numeric',
            'concepto' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);
        
        $validated['contrato_id'] = $contratoId;
        $validated['valor_neto'] = $validated['valor_bruto'] 
            - ($validated['retencion_fuente'] ?? 0)
            - ($validated['reteica'] ?? 0)
            - ($validated['reteiva'] ?? 0)
            - ($validated['otras_deducciones'] ?? 0);
        
        \App\Modules\Nomina\Models\PagoContrato::create($validated);
        
        return redirect()->route('nomina.contratos.pagos.index', $contratoId)
            ->with('success', 'Pago registrado exitosamente');
    }

    /**
     * Vista para crear novedad
     */
    public function crearNovedad()
    {
        $empleados = \App\Modules\Nomina\Models\Empleado::where('estado', 'activo')
            ->orderBy('primer_apellido')
            ->orderBy('primer_nombre')
            ->get();
        
        $conceptos = \App\Modules\Nomina\Models\ConceptoNomina::where('activo', true)
            ->orderBy('codigo')
            ->get();
        
        $periodos = \App\Modules\Nomina\Models\PeriodoNomina::where('estado', 'abierto')
            ->orderBy('fecha_inicio', 'desc')
            ->get();
        
        return view('nomina.livewire.nomina.create-novedad', compact('empleados', 'conceptos', 'periodos'));
    }

    /**
     * Guardar novedad
     */
    public function guardarNovedad(Request $request)
    {
        $validated = $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'concepto_nomina_id' => 'required|exists:conceptos_nomina,id',
            'fecha_novedad' => 'required|date',
            'cantidad' => 'required|numeric|min:0',
            'valor_unitario' => 'required|numeric|min:0',
            'periodo_nomina_id' => 'nullable|exists:periodos_nomina,id',
            'observaciones' => 'nullable|string',
            'procesada' => 'nullable|boolean',
        ]);
        
        // Calcular valor total
        $validated['valor_total'] = $validated['cantidad'] * $validated['valor_unitario'];
        $validated['procesada'] = $request->has('procesada');
        $validated['estado'] = 'pendiente';
        $validated['created_by'] = auth()->id();
        
        \App\Modules\Nomina\Models\NovedadNomina::create($validated);
        
        // Si es "guardar y crear otra"
        if ($request->accion === 'guardar_nuevo') {
            return redirect()->route('nomina.novedades.crear')
                ->with('success', 'Novedad creada exitosamente');
        }
        
        return redirect()->route('nomina.novedades.index')
            ->with('success', 'Novedad creada exitosamente');
    }

    /**
     * Vista para editar novedad
     */
    public function editarNovedad($id)
    {
        $novedad = \App\Modules\Nomina\Models\NovedadNomina::findOrFail($id);
        
        $empleados = \App\Modules\Nomina\Models\Empleado::where('estado', 'activo')
            ->orderBy('primer_apellido')
            ->orderBy('primer_nombre')
            ->get();
        
        $conceptos = \App\Modules\Nomina\Models\ConceptoNomina::where('activo', true)
            ->orderBy('codigo')
            ->get();
        
        $periodos = \App\Modules\Nomina\Models\PeriodoNomina::where('estado', 'abierto')
            ->orderBy('fecha_inicio', 'desc')
            ->get();
        
        return view('nomina.livewire.nomina.edit-novedad', compact('novedad', 'empleados', 'conceptos', 'periodos'));
    }

    /**
     * Actualizar novedad
     */
    public function actualizarNovedad(Request $request, $id)
    {
        $novedad = \App\Modules\Nomina\Models\NovedadNomina::findOrFail($id);
        
        $validated = $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'concepto_nomina_id' => 'required|exists:conceptos_nomina,id',
            'fecha_novedad' => 'required|date',
            'cantidad' => 'required|numeric|min:0',
            'valor_unitario' => 'required|numeric|min:0',
            'periodo_nomina_id' => 'nullable|exists:periodos_nomina,id',
            'observaciones' => 'nullable|string',
            'procesada' => 'nullable|boolean',
        ]);
        
        // Calcular valor total
        $validated['valor_total'] = $validated['cantidad'] * $validated['valor_unitario'];
        $validated['procesada'] = $request->has('procesada');
        $validated['updated_by'] = auth()->id();
        
        $novedad->update($validated);
        
        return redirect()->route('nomina.novedades.index')
            ->with('success', 'Novedad actualizada exitosamente');
    }

    /**
     * Eliminar novedad
     */
    public function eliminarNovedad($id)
    {
        $novedad = \App\Modules\Nomina\Models\NovedadNomina::findOrFail($id);
        
        // Verificar si está procesada
        if ($novedad->procesada) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar una novedad que ya ha sido procesada');
        }
        
        // Verificar si está asociada a una nómina
        if ($novedad->nomina_id) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar una novedad asociada a una nómina');
        }
        
        $novedad->delete();
        
        return redirect()->route('nomina.novedades.index')
            ->with('success', 'Novedad eliminada exitosamente');
    }

    /**
     * Mostrar formulario de importación
     */
    public function importarNovedades()
    {
        $step = 1; // Paso inicial
        return view('nomina.livewire.nomina.importacion-novedades', compact('step'));
    }

    /**
     * Procesar archivo de importación
     */
    public function procesarImportacion(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);
        
        try {
            $archivo = $request->file('archivo');
            
            // Leer el archivo Excel
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivo->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Validar que tenga datos
            if (count($rows) <= 1) {
                return redirect()->route('nomina.novedades.importar')
                    ->with('error', 'El archivo está vacío o solo contiene encabezados');
            }
            
            // Obtener y normalizar encabezados
            $headers = $rows[0];
            $headersMap = $this->mapearEncabezados($headers);
            
            \Log::info('Encabezados detectados:', $headersMap);
            
            // Validar que se encontraron todos los encabezados requeridos
            $requeridos = ['documento', 'concepto', 'fecha', 'cantidad', 'valor_unitario'];
            $faltantes = [];
            
            foreach ($requeridos as $req) {
                if (!isset($headersMap[$req]) || $headersMap[$req] === null) {
                    $faltantes[] = $req;
                }
            }
            
            if (!empty($faltantes)) {
                return redirect()->route('nomina.novedades.importar')
                    ->with('error', 'No se pudieron identificar las siguientes columnas: ' . implode(', ', $faltantes) . '. Verifique que el archivo tenga los encabezados correctos.');
            }
            
            // Procesar filas
            $novedades = [];
            $errores = [];
            
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                // Saltar filas vacías
                if ($this->esFilaVacia($row)) {
                    continue;
                }
                
                $novedad = $this->procesarFilaImportacion($row, $headersMap, $i + 1);
                
                if ($novedad['valido']) {
                    $novedades[] = $novedad;
                } else {
                    $errores[] = $novedad;
                }
            }
            
            \Log::info('Procesamiento completado', [
                'novedades_validas' => count($novedades),
                'errores' => count($errores),
            ]);
            
            // Guardar en sesión
            session(['novedades_importacion' => [
                'novedades' => $novedades,
                'errores' => $errores,
            ]]);
            
            $step = 2;
            return view('nomina.livewire.nomina.importacion-novedades', compact('step', 'novedades', 'errores'));
            
        } catch (\Exception $e) {
            \Log::error('Error procesando importación: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return redirect()->route('nomina.novedades.importar')
                ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Mapear encabezados del archivo
     */
    private function mapearEncabezados($headers)
    {
        $map = [
            'documento' => null,
            'concepto' => null,
            'fecha' => null,
            'cantidad' => null,
            'valor_unitario' => null,
            'observaciones' => null,
        ];
        
        foreach ($headers as $index => $header) {
            $headerNormalizado = strtolower(trim(str_replace([' ', '_', '-'], '', $header)));
            
            // Documento
            if (in_array($headerNormalizado, ['documento', 'doc', 'cedula', 'cc', 'identificacion'])) {
                $map['documento'] = $index;
            }
            // Concepto
            elseif (in_array($headerNormalizado, ['concepto', 'codigo', 'codigoconcepto'])) {
                $map['concepto'] = $index;
            }
            // Fecha
            elseif (in_array($headerNormalizado, ['fecha', 'fechanovedad', 'fechadelanomalia'])) {
                $map['fecha'] = $index;
            }
            // Cantidad
            elseif (in_array($headerNormalizado, ['cantidad', 'cant', 'horas', 'dias', 'qty'])) {
                $map['cantidad'] = $index;
            }
            // Valor Unitario
            elseif (in_array($headerNormalizado, ['valorunitario', 'valorunit', 'valor', 'precio', 'valorunidad'])) {
                $map['valor_unitario'] = $index;
            }
            // Observaciones
            elseif (in_array($headerNormalizado, ['observaciones', 'obs', 'nota', 'notas', 'descripcion'])) {
                $map['observaciones'] = $index;
            }
        }
        
        return $map;
    }

    /**
     * Verificar si una fila está vacía
     */
    private function esFilaVacia($row)
    {
        foreach ($row as $cell) {
            if (!empty(trim($cell))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Procesar una fila del archivo
     */
    private function procesarFilaImportacion($row, $headersMap, $filaNumero)
    {
        $errores = [];
        $datos = [
            'fila' => $filaNumero,
            'valido' => false,
        ];
        
        try {
            // 1. Documento
            $documento = isset($headersMap['documento']) ? trim($row[$headersMap['documento']] ?? '') : '';
            
            if (empty($documento)) {
                $errores[] = 'Documento vacío';
            } else {
                $empleado = \App\Modules\Nomina\Models\Empleado::where('numero_documento', $documento)
                    ->first();
                
                if (!$empleado) {
                    $errores[] = "Empleado con documento {$documento} no encontrado";
                } else {
                    $datos['empleado_id'] = $empleado->id;
                    $datos['empleado'] = $empleado->nombre_completo;
                    $datos['documento'] = $documento;
                }
            }
            
            // 2. Concepto
            $codigoConcepto = isset($headersMap['concepto']) ? trim($row[$headersMap['concepto']] ?? '') : '';
            
            if (empty($codigoConcepto)) {
                $errores[] = 'Código de concepto vacío';
            } else {
                $concepto = \App\Modules\Nomina\Models\ConceptoNomina::where('codigo', $codigoConcepto)
                    ->where('activo', true)
                    ->first();
                
                if (!$concepto) {
                    // Intentar buscar por nombre si no encuentra por código
                    $concepto = \App\Modules\Nomina\Models\ConceptoNomina::where('nombre', 'like', "%{$codigoConcepto}%")
                        ->where('activo', true)
                        ->first();
                }
                
                if (!$concepto) {
                    $errores[] = "Concepto '{$codigoConcepto}' no encontrado o inactivo";
                } else {
                    $datos['concepto_id'] = $concepto->id;
                    $datos['concepto'] = $concepto->nombre;
                    $datos['codigo_concepto'] = $concepto->codigo;
                }
            }
            
            // 3. Fecha
            $fechaRaw = isset($headersMap['fecha']) ? ($row[$headersMap['fecha']] ?? '') : '';
            
            if (empty($fechaRaw)) {
                $errores[] = 'Fecha vacía';
            } else {
                try {
                    // Detectar si es fecha numérica de Excel
                    if (is_numeric($fechaRaw) && $fechaRaw > 25569) { // 25569 = 1970-01-01 en Excel
                        $fecha = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fechaRaw);
                        $datos['fecha'] = $fecha->format('Y-m-d');
                    } else {
                        // Intentar parsear como texto
                        $fecha = \Carbon\Carbon::parse($fechaRaw);
                        $datos['fecha'] = $fecha->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    $errores[] = "Fecha inválida: {$fechaRaw}";
                }
            }
            
            // 4. Cantidad
            $cantidad = isset($headersMap['cantidad']) ? floatval($row[$headersMap['cantidad']] ?? 0) : 0;
            
            if ($cantidad < 0) {
                $errores[] = 'Cantidad no puede ser negativa';
            }
            $datos['cantidad'] = $cantidad;
            
            // 5. Valor Unitario
            $valorUnitario = isset($headersMap['valor_unitario']) ? floatval($row[$headersMap['valor_unitario']] ?? 0) : 0;
            
            if ($valorUnitario < 0) {
                $errores[] = 'Valor unitario no puede ser negativo';
            }
            $datos['valor_unitario'] = $valorUnitario;
            
            // 6. Observaciones (opcional)
            $datos['observaciones'] = isset($headersMap['observaciones']) 
                ? trim($row[$headersMap['observaciones']] ?? '') 
                : '';
            
            // Calcular valor total
            $datos['valor_total'] = $cantidad * $valorUnitario;
            
            // Determinar si es válida
            $datos['valido'] = empty($errores);
            $datos['errores'] = $errores;
            
        } catch (\Exception $e) {
            $errores[] = 'Error: ' . $e->getMessage();
            $datos['valido'] = false;
            $datos['errores'] = $errores;
        }
        
        return $datos;
    }

    /**
     * Descargar plantilla Excel para importación de novedades
     */
    public function descargarPlantilla()
    {
        // Datos de ejemplo para la plantilla
        $headers = [
            'Documento',
            'Concepto',
            'Fecha',
            'Cantidad',
            'Valor Unitario',
            'Observaciones',
            'Período'
        ];
        
        $ejemplos = [
            [
                '1234567890',
                'HED',
                date('Y-m-d'),
                '8',
                '15000',
                'Horas extras diurnas del día X',
                'Febrero 2026'
            ],
            [
                '9876543210',
                'INC',
                date('Y-m-d'),
                '3',
                '0',
                'Incapacidad médica',
                'Febrero 2026'
            ]
        ];
        
        // Crear archivo CSV simple
        $filename = 'plantilla_novedades_' . date('Y-m-d') . '.csv';
        $handle = fopen('php://temp', 'r+');
        
        // Escribir BOM para UTF-8
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Escribir headers
        fputcsv($handle, $headers, ';');
        
        // Escribir ejemplos
        foreach ($ejemplos as $ejemplo) {
            fputcsv($handle, $ejemplo, ';');
        }
        
        // Agregar filas vacías para llenar
        for ($i = 0; $i < 10; $i++) {
            fputcsv($handle, array_fill(0, count($headers), ''), ';');
        }
        
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        
        return response($content)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    /**
     * Detalle de un asiento contable
     */
    public function detalleAsiento($id)
    {
        // Simular datos del asiento
        $asiento = (object)[
            'id' => $id,
            'numero_asiento' => 'ASI-2026-001',
            'fecha_asiento' => now(),
            'tipo_asiento' => 'causacion_nomina',
            'total_debitos' => 50000000,
            'total_creditos' => 50000000,
            'estado' => 'contabilizado',
            'descripcion' => 'Causación nómina febrero 2026',
        ];
        
        // Simular detalles del asiento
        $detalles = [
            [
                'cuenta' => '510506 - Sueldos',
                'tercero' => 'Empleados',
                'debito' => 40000000,
                'credito' => 0,
            ],
            [
                'cuenta' => '237005 - Salud por pagar',
                'tercero' => 'EPS',
                'debito' => 0,
                'credito' => 3400000,
            ],
            [
                'cuenta' => '237010 - Pensión por pagar',
                'tercero' => 'Fondo pensión',
                'debito' => 0,
                'credito' => 6400000,
            ],
            [
                'cuenta' => '2505 - Salarios por pagar',
                'tercero' => 'Empleados',
                'debito' => 0,
                'credito' => 30200000,
            ],
        ];
        
        return view('nomina.livewire.provisiones.detalle-asiento', compact('asiento', 'detalles'));
    }

    /**
     * Aprobar asiento contable
     */
    public function aprobarAsiento($id)
    {
        return redirect()->back()->with('success', 'Asiento aprobado exitosamente');
    }

    /**
     * Contabilizar asiento
     */
    public function contabilizarAsiento($id)
    {
        return redirect()->back()->with('success', 'Asiento contabilizado exitosamente');
    }

    /**
     * Anular asiento
     */
    public function anularAsiento(Request $request, $id)
    {
        return redirect()->back()->with('success', 'Asiento anulado exitosamente');
    }

    /**
     * Confirmar y guardar las novedades importadas
     */
    public function confirmarImportacionNovedades(Request $request)
    {
        $datosImportacion = session('novedades_importacion');
        
        if (!$datosImportacion || empty($datosImportacion['novedades'])) {
            return redirect()->route('nomina.novedades.importar')
                ->with('error', 'No hay datos para importar');
        }
        
        try {
            \DB::beginTransaction();
            
            $importadas = 0;
            
            foreach ($datosImportacion['novedades'] as $novedad) {
                \App\Modules\Nomina\Models\NovedadNomina::create([
                    'empleado_id' => $novedad['empleado_id'],
                    'concepto_nomina_id' => $novedad['concepto_id'],
                    'fecha_novedad' => $novedad['fecha'],
                    'cantidad' => $novedad['cantidad'],
                    'valor_unitario' => $novedad['valor_unitario'],
                    'valor_total' => $novedad['valor_total'],
                    'observaciones' => $novedad['observaciones'] ?? '',
                    'estado' => 'pendiente', // ✅ AGREGAR
                    'procesada' => false,
                    'requiere_aprobacion' => false, // ✅ AGREGAR
                    'created_by' => auth()->id(),
                ]);
                
                $importadas++;
            }
            
            \DB::commit();
            
            // Limpiar sesión
            session()->forget('novedades_importacion');
            
            return redirect()->route('nomina.novedades.index')
                ->with('success', "Se importaron exitosamente {$importadas} novedades");
                
        } catch (\Exception $e) {
            \DB::rollBack();
            
            \Log::error('Error confirmando importación: ' . $e->getMessage());
            \Log::error('Datos que causaron el error:', [
                'novedad' => $novedad ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('nomina.novedades.importar')
                ->with('error', 'Error al guardar las novedades: ' . $e->getMessage());
        }
    }

}