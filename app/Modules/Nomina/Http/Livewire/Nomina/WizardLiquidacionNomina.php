<?php
namespace App\Modules\Nomina\Http\Livewire\Nomina;

use Livewire\Component;
use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Models\TipoNomina;
use App\Modules\Nomina\Models\PeriodoNomina;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Services\LiquidacionService;

class WizardLiquidacionNomina extends Component
{
    public $step = 1;
    public $totalSteps = 5;
    
    // Paso 1: Información básica
    public $tipo_nomina_id;
    public $periodo_nomina_id;
    public $nombre;
    public $fecha_pago;
    
    // Paso 2: Selección de empleados
    public $empleadosSeleccionados = [];
    public $todosLosEmpleados = false;
    
    // Paso 3: Novedades
    public $novedadesPendientes = [];
    
    // Paso 4: Preliquidación
    public $detallesCalculados = [];
    
    // Paso 5: Confirmación
    public $nominaId;
    public $resultado;

    public function mount()
    {
        // Cargar período actual si existe
        $periodoActual = PeriodoNomina::abiertos()
            ->where('anio', now()->year)
            ->where('mes', now()->month)
            ->first();
        
        if ($periodoActual) {
            $this->periodo_nomina_id = $periodoActual->id;
        }
    }

    public function render()
    {
        $tiposNomina = TipoNomina::activos()->get();
        $periodos = PeriodoNomina::abiertos()->get();
        $empleados = Empleado::activos()->get();

        return view('nomina.livewire.nomina.wizard-liquidacion', [
            'tiposNomina' => $tiposNomina,
            'periodos' => $periodos,
            'empleados' => $empleados,
        ]);
    }

    public function nextStep()
    {
        // Validar el paso actual
        $this->validateCurrentStep();
        
        if ($this->step < $this->totalSteps) {
            $this->step++;
            
            // Ejecutar acciones específicas al avanzar
            $this->executeStepActions();
        }
    }

    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    protected function validateCurrentStep()
    {
        switch ($this->step) {
            case 1:
                $this->validate([
                    'tipo_nomina_id' => 'required|exists:tipos_nomina,id',
                    'periodo_nomina_id' => 'required|exists:periodos_nomina,id',
                    'nombre' => 'required|string|max:200',
                    'fecha_pago' => 'required|date',
                ]);
                break;
            
            case 2:
                if (empty($this->empleadosSeleccionados) && !$this->todosLosEmpleados) {
                    throw new \Exception('Debe seleccionar al menos un empleado');
                }
                break;
        }
    }

    protected function executeStepActions()
    {
        switch ($this->step) {
            case 3:
                // Cargar novedades pendientes
                $this->cargarNovedadesPendientes();
                break;
            
            case 4:
                // Realizar preliquidación
                $this->preliquidar();
                break;
        }
    }

    protected function cargarNovedadesPendientes()
    {
        // Lógica para cargar novedades
    }

    protected function preliquidar()
    {
        // Crear nómina temporal
        $nomina = new Nomina([
            'tipo_nomina_id' => $this->tipo_nomina_id,
            'periodo_nomina_id' => $this->periodo_nomina_id,
            'nombre' => $this->nombre,
            'fecha_pago' => $this->fecha_pago,
        ]);
        
        // Calcular sin guardar
        $liquidacionService = new LiquidacionService();
        // ... preliquidar empleados
    }

    public function confirmarLiquidacion()
    {
        try {
            // Crear nómina definitiva
            $nomina = Nomina::create([
                'numero_nomina' => $this->generarNumeroNomina(),
                'tipo_nomina_id' => $this->tipo_nomina_id,
                'periodo_nomina_id' => $this->periodo_nomina_id,
                'nombre' => $this->nombre,
                'fecha_pago' => $this->fecha_pago,
                'estado' => 'borrador',
            ]);

            // Liquidar
            $liquidacionService = new LiquidacionService();
            $resultado = $liquidacionService->liquidar($nomina, $this->empleadosSeleccionados);

            $this->nominaId = $nomina->id;
            $this->resultado = $resultado;
            $this->step = 5;

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    protected function generarNumeroNomina()
    {
        $periodo = PeriodoNomina::find($this->periodo_nomina_id);
        $consecutivo = Nomina::where('periodo_nomina_id', $this->periodo_nomina_id)->count() + 1;
        
        return sprintf('NOM-%04d-%02d-%03d', $periodo->anio, $periodo->mes, $consecutivo);
    }
}