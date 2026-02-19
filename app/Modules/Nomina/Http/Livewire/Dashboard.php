<?php

namespace App\Modules\Nomina\Http\Livewire;

use Livewire\Component;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Models\Contrato;
use App\Modules\Nomina\Models\ProvisionEmpleado;
use App\Modules\Nomina\Models\NovedadNomina;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $periodoSeleccionado;
    public $metricas = [];
    public $nominasRecientes = [];
    public $novedadesPendientes = [];
    public $contratosProximosVencer = [];
    public $distribucionEmpleados = [];
    public $evolucionNomina = [];

    public function mount()
    {
        $this->periodoSeleccionado = now()->format('Y-m');
        $this->cargarDatos();
    }

    public function render()
    {
        return view('nomina.livewire.dashboard', [
            'metricas' => $this->metricas,
            'nominasRecientes' => $this->nominasRecientes,
            'novedadesPendientes' => $this->novedadesPendientes,
            'contratosProximosVencer' => $this->contratosProximosVencer,
            'distribucionEmpleados' => $this->distribucionEmpleados,
            'evolucionNomina' => $this->evolucionNomina,
        ]);
    }

    public function cargarDatos()
    {
        $this->calcularMetricas();
        $this->cargarNominasRecientes();
        $this->cargarNovedadesPendientes();
        $this->cargarContratosProximosVencer();
        $this->cargarDistribucionEmpleados();
        $this->cargarEvolucionNomina();
    }

    protected function calcularMetricas()
    {
        // Total de empleados activos
        $totalEmpleadosActivos = Empleado::activos()->count();
        
        // Total de empleados por tipo de contrato
        $porTipoContrato = Empleado::activos()
            ->select('tipo_contrato', DB::raw('count(*) as total'))
            ->groupBy('tipo_contrato')
            ->get()
            ->pluck('total', 'tipo_contrato');

        // Nómina del mes actual
        $nominaMesActual = Nomina::whereYear('fecha_inicio', now()->year)
            ->whereMonth('fecha_inicio', now()->month)
            ->first();

        // Contratos activos
        $contratosActivos = Contrato::activos()->count();

        // Novedades pendientes
        $novedadesPendientes = NovedadNomina::pendientes()->count();

        // Provisiones totales
        $provisionesTotales = ProvisionEmpleado::where('anio', now()->year)
            ->sum(DB::raw('saldo_cesantias + saldo_intereses + saldo_prima + saldo_vacaciones'));

        // Costo total nómina del mes
        $costoNominaMes = $nominaMesActual?->costo_total_empleador ?? 0;

        // Tendencia vs mes anterior
        $nominaMesAnterior = Nomina::whereYear('fecha_inicio', now()->subMonth()->year)
            ->whereMonth('fecha_inicio', now()->subMonth()->month)
            ->first();

        $tendenciaNomina = 0;
        if ($nominaMesAnterior && $nominaMesActual) {
            $diferencia = $nominaMesActual->total_neto - $nominaMesAnterior->total_neto;
            $tendenciaNomina = ($diferencia / $nominaMesAnterior->total_neto) * 100;
        }

        $this->metricas = [
            'empleados_activos' => [
                'total' => $totalEmpleadosActivos,
                'indefinidos' => $porTipoContrato['indefinido'] ?? 0,
                'fijos' => $porTipoContrato['fijo'] ?? 0,
                'prestacion_servicios' => $porTipoContrato['prestacion_servicios'] ?? 0,
            ],
            'nomina_mes_actual' => [
                'existe' => $nominaMesActual !== null,
                'numero' => $nominaMesActual?->numero_nomina,
                'estado' => $nominaMesActual?->estado,
                'total_neto' => $nominaMesActual?->total_neto ?? 0,
                'costo_empleador' => $costoNominaMes,
                'tendencia' => round($tendenciaNomina, 2),
            ],
            'contratos' => [
                'activos' => $contratosActivos,
                'proximos_vencer' => Contrato::proximosVencer(30)->count(),
            ],
            'novedades_pendientes' => $novedadesPendientes,
            'provisiones_totales' => $provisionesTotales,
        ];
    }

    protected function cargarNominasRecientes()
    {
        $this->nominasRecientes = Nomina::with('tipoNomina', 'periodo')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function($nomina) {
                return [
                    'id' => $nomina->id,
                    'numero' => $nomina->numero_nomina,
                    'nombre' => $nomina->nombre,
                    'periodo' => $nomina->periodo->nombre ?? '',
                    'estado' => $nomina->estado,
                    'total_neto' => $nomina->total_neto,
                    'empleados' => $nomina->numero_empleados,
                    'fecha' => $nomina->created_at->format('d/m/Y'),
                ];
            })
            ->toArray();
    }

    protected function cargarNovedadesPendientes()
    {
        $this->novedadesPendientes = NovedadNomina::with('empleado', 'concepto')
            ->pendientes()
            ->orderBy('fecha_novedad')
            ->limit(10)
            ->get()
            ->map(function($novedad) {
                return [
                    'id' => $novedad->id,
                    'empleado' => $novedad->empleado->nombre_completo,
                    'concepto' => $novedad->concepto->nombre,
                    'valor' => $novedad->valor_total,
                    'fecha' => $novedad->fecha_novedad->format('d/m/Y'),
                    'requiere_aprobacion' => $novedad->requiere_aprobacion,
                ];
            })
            ->toArray();
    }

    protected function cargarContratosProximosVencer()
    {
        $this->contratosProximosVencer = Contrato::with('centroCosto')
            ->proximosVencer(30)
            ->orderBy('fecha_fin')
            ->limit(10)
            ->get()
            ->map(function($contrato) {
                return [
                    'id' => $contrato->id,
                    'numero' => $contrato->numero_contrato,
                    'contratista' => $contrato->nombre_contratista,
                    'fecha_fin' => $contrato->fecha_fin->format('d/m/Y'),
                    'dias_restantes' => $contrato->dias_restantes,
                    'valor_total' => $contrato->valor_total,
                    'saldo_pendiente' => $contrato->saldo_pendiente,
                ];
            })
            ->toArray();
    }

    protected function cargarDistribucionEmpleados()
    {
        // Por tipo de contrato
        $porTipo = Empleado::activos()
            ->select('tipo_contrato', DB::raw('count(*) as total'))
            ->groupBy('tipo_contrato')
            ->get();

        // Por estado
        $porEstado = Empleado::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();

        // Por rango salarial
        $smlv = config('nomina.smlv.valor_actual', 1300000);
        $porSalario = [
            'hasta_2_smlv' => Empleado::activos()->where('salario_basico', '<=', 2 * $smlv)->count(),
            '2_a_4_smlv' => Empleado::activos()->whereBetween('salario_basico', [2 * $smlv, 4 * $smlv])->count(),
            '4_a_10_smlv' => Empleado::activos()->whereBetween('salario_basico', [4 * $smlv, 10 * $smlv])->count(),
            'mas_10_smlv' => Empleado::activos()->where('salario_basico', '>', 10 * $smlv)->count(),
        ];

        $this->distribucionEmpleados = [
            'por_tipo_contrato' => $porTipo->toArray(),
            'por_estado' => $porEstado->toArray(),
            'por_rango_salarial' => $porSalario,
        ];
    }

    protected function cargarEvolucionNomina()
    {
        // Últimos 6 meses
        $evolucion = Nomina::select(
                DB::raw('YEAR(fecha_inicio) as anio'),
                DB::raw('MONTH(fecha_inicio) as mes'),
                DB::raw('SUM(total_neto) as total_neto'),
                DB::raw('SUM(costo_total_empleador) as costo_total'),
                DB::raw('SUM(numero_empleados) as empleados')
            )
            ->where('estado', '!=', 'anulada')
            ->where('fecha_inicio', '>=', now()->subMonths(6))
            ->groupBy('anio', 'mes')
            ->orderBy('anio')
            ->orderBy('mes')
            ->get();

        $this->evolucionNomina = $evolucion->map(function($item) {
            return [
                'periodo' => sprintf('%04d-%02d', $item->anio, $item->mes),
                'mes_nombre' => $this->nombreMes($item->mes),
                'total_neto' => $item->total_neto,
                'costo_total' => $item->costo_total,
                'empleados' => $item->empleados,
            ];
        })->toArray();
    }

    protected function nombreMes($mes)
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $meses[$mes] ?? '';
    }

    public function cambiarPeriodo($periodo)
    {
        $this->periodoSeleccionado = $periodo;
        $this->cargarDatos();
    }
}