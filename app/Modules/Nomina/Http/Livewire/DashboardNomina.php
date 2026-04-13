<?php

namespace App\Modules\Nomina\Http\Livewire;

use Livewire\Component;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Models\Contrato;
use App\Modules\Nomina\Models\NovedadNomina;
use App\Modules\Nomina\Models\ProvisionEmpleado;
use Illuminate\Support\Facades\DB;

class DashboardNomina extends Component
{
    public $mesActual;
    public $anioActual;
    
    // Estadísticas
    public $totalEmpleados;
    public $empleadosActivos;
    public $empleadosInactivos;
    public $contratosActivos;
    public $contratosProximosVencer;
    public $novedadesPendientes;
    public $nominasMesActual;
    
    // Datos para gráficas
    public $datosNominaMensual = [];
    public $datosPorCentroCosto = [];
    public $datosProvisiones = [];
    public $evolucionNomina = [];
    
    // Alertas
    public $alertas = [];

    public function mount()
    {
        $this->mesActual = now()->month;
        $this->anioActual = now()->year;
        $this->cargarDatos();
    }

    public function render()
    {
        return view('nomina.livewire.dashboard-nomina');
    }

    public function cargarDatos()
    {
        $this->cargarEstadisticasEmpleados();
        $this->cargarEstadisticasContratos();
        $this->cargarEstadisticasNovedades();
        $this->cargarDatosNomina();
        $this->cargarDatosProvisiones();
        $this->generarAlertas();
    }

    protected function cargarEstadisticasEmpleados()
    {
        $this->totalEmpleados = Empleado::count();
        $this->empleadosActivos = Empleado::activos()->count();
        $this->empleadosInactivos = Empleado::whereIn('estado', ['inactivo', 'retirado'])->count();
    }

    protected function cargarEstadisticasContratos()
    {
        $this->contratosActivos = Contrato::activos()->count();
        $this->contratosProximosVencer = Contrato::proximosVencer(30)->count();
    }

    protected function cargarEstadisticasNovedades()
    {
        $this->novedadesPendientes = NovedadNomina::pendientes()
            ->whereMonth('fecha', $this->mesActual)
            ->whereYear('fecha', $this->anioActual)
            ->count();
    }

    protected function cargarDatosNomina()
    {
        // Nóminas del mes actual
        $nominasMes = Nomina::whereMonth('fecha_inicio', $this->mesActual)
            ->whereYear('fecha_inicio', $this->anioActual)
            ->get();

        $this->nominasMesActual = $nominasMes->count();

        // Datos para gráfica de nómina mensual
        $this->datosNominaMensual = [
            'labels' => ['Devengados', 'Deducciones', 'Neto', 'Costo Empleador'],
            'datasets' => [[
                'label' => 'Valores en Millones',
                'data' => [
                    $nominasMes->sum('total_devengado') / 1000000,
                    $nominasMes->sum('total_deducciones') / 1000000,
                    $nominasMes->sum('total_neto') / 1000000,
                    $nominasMes->sum(fn($n) => $n->costo_total_empleador) / 1000000,
                ],
                'backgroundColor' => ['#10B981', '#EF4444', '#3B82F6', '#F59E0B'],
            ]]
        ];

        // Evolución de nómina (últimos 6 meses)
        $this->evolucionNomina = Nomina::select(
                DB::raw('YEAR(fecha_inicio) as anio'),
                DB::raw('MONTH(fecha_inicio) as mes'),
                DB::raw('SUM(total_neto) as total')
            )
            ->where('fecha_inicio', '>=', now()->subMonths(6))
            ->groupBy('anio', 'mes')
            ->orderBy('anio')
            ->orderBy('mes')
            ->get()
            ->map(function($item) {
                return [
                    'periodo' => date('M Y', mktime(0, 0, 0, $item->mes, 1, $item->anio)),
                    'total' => $item->total / 1000000,
                ];
            });

        // Datos por centro de costo
        $this->datosPorCentroCosto = Empleado::activos()
            ->with('centrosCostoActivos')
            ->get()
            ->flatMap(function($empleado) {
                return $empleado->centrosCostoActivos->map(function($centro) use ($empleado) {
                    return [
                        'centro' => $centro->nombre,
                        'empleados' => 1,
                        'costo' => $empleado->salario_basico * ($centro->pivot->porcentaje / 100),
                    ];
                });
            })
            ->groupBy('centro')
            ->map(function($grupo, $centro) {
                return [
                    'centro' => $centro,
                    'empleados' => $grupo->sum('empleados'),
                    'costo' => $grupo->sum('costo') / 1000000,
                ];
            })
            ->values();
    }

    protected function cargarDatosProvisiones()
    {
        $provisiones = ProvisionEmpleado::where('anio', $this->anioActual)
            ->select(
                DB::raw('SUM(saldo_cesantias) as total_cesantias'),
                DB::raw('SUM(saldo_intereses) as total_intereses'),
                DB::raw('SUM(saldo_prima) as total_prima'),
                DB::raw('SUM(saldo_vacaciones) as total_vacaciones')
            )
            ->first();

        $this->datosProvisiones = [
            'labels' => ['Cesantías', 'Intereses', 'Prima', 'Vacaciones'],
            'datasets' => [[
                'label' => 'Provisiones en Millones',
                'data' => [
                    ($provisiones->total_cesantias ?? 0) / 1000000,
                    ($provisiones->total_intereses ?? 0) / 1000000,
                    ($provisiones->total_prima ?? 0) / 1000000,
                    ($provisiones->total_vacaciones ?? 0) / 1000000,
                ],
                'backgroundColor' => ['#8B5CF6', '#EC4899', '#F59E0B', '#10B981'],
            ]]
        ];
    }

    protected function generarAlertas()
    {
        $this->alertas = [];

        // Alertar contratos próximos a vencer
        if ($this->contratosProximosVencer > 0) {
            $this->alertas[] = [
                'tipo' => 'warning',
                'icono' => 'exclamation-triangle',
                'titulo' => 'Contratos por Vencer',
                'mensaje' => "{$this->contratosProximosVencer} contrato(s) vencen en los próximos 30 días",
                'accion' => route('nomina.contratos.index', ['filter' => 'proximos-vencer']),
                'textoAccion' => 'Ver Contratos',
            ];
        }

        // Alertar novedades pendientes
        if ($this->novedadesPendientes > 0) {
            $this->alertas[] = [
                'tipo' => 'info',
                'icono' => 'information-circle',
                'titulo' => 'Novedades Pendientes',
                'mensaje' => "{$this->novedadesPendientes} novedad(es) pendiente(s) de procesar",
                'accion' => route('nomina.novedades.index', ['estado' => 'pendiente']),
                'textoAccion' => 'Ver Novedades',
            ];
        }

        // Alertar si no hay nómina del mes
        if ($this->nominasMesActual === 0 && now()->day > 5) {
            $this->alertas[] = [
                'tipo' => 'error',
                'icono' => 'exclamation',
                'titulo' => 'Nómina Pendiente',
                'mensaje' => 'No se ha liquidado la nómina del mes actual',
                'accion' => route('nomina.liquidacion.wizard'),
                'textoAccion' => 'Liquidar Nómina',
            ];
        }

        // Alertar empleados sin seguridad social
        $sinSeguridadSocial = Empleado::activos()
            ->whereNull('eps')
            ->orWhereNull('fondo_pension')
            ->count();

        if ($sinSeguridadSocial > 0) {
            $this->alertas[] = [
                'tipo' => 'warning',
                'icono' => 'shield-exclamation',
                'titulo' => 'Seguridad Social Incompleta',
                'mensaje' => "{$sinSeguridadSocial} empleado(s) sin datos de seguridad social completos",
                'accion' => route('nomina.empleados.index', ['filter' => 'sin-ss']),
                'textoAccion' => 'Revisar',
            ];
        }
    }

    public function refrescar()
    {
        $this->cargarDatos();
        session()->flash('success', 'Datos actualizados');
    }

    public function cambiarPeriodo($mes, $anio)
    {
        $this->mesActual = $mes;
        $this->anioActual = $anio;
        $this->cargarDatos();
    }
}