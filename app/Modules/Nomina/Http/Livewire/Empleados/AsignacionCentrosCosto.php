<?php

namespace App\Modules\Nomina\Http\Livewire\Empleados;

use Livewire\Component;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\CentroCosto;
use Illuminate\Support\Facades\DB;

class AsignacionCentrosCosto extends Component
{
    public $empleadoId;
    public $empleado;
    public $centrosDisponibles;
    public $asignaciones = [];
    public $showModal = false;
    
    // Nueva asignación
    public $centroCostoId;
    public $porcentaje = 100;

    protected $rules = [
        'centroCostoId' => 'required|exists:centros_costo,id',
        'porcentaje' => 'required|numeric|min:0.01|max:100',
    ];

    protected $messages = [
        'centroCostoId.required' => 'Debe seleccionar un centro de costo',
        'porcentaje.required' => 'El porcentaje es obligatorio',
        'porcentaje.min' => 'El porcentaje debe ser mayor a 0',
        'porcentaje.max' => 'El porcentaje no puede superar 100%',
    ];

    public function mount($empleadoId)
    {
        $this->empleadoId = $empleadoId;
        $this->empleado = Empleado::findOrFail($empleadoId);
        $this->cargarDatos();
    }

    public function render()
    {
        return view('nomina.livewire.empleados.asignacion-centros-costo');
    }

    public function cargarDatos()
    {
        // Cargar centros de costo activos
        $this->centrosDisponibles = CentroCosto::activos()
            ->orderBy('codigo')
            ->get();

        // Cargar asignaciones actuales
        $this->asignaciones = $this->empleado->centrosCostoActivos()
            ->withPivot('porcentaje', 'fecha_inicio', 'fecha_fin')
            ->get()
            ->map(function($centro) {
                return [
                    'id' => $centro->id,
                    'codigo' => $centro->codigo,
                    'nombre' => $centro->nombre,
                    'porcentaje' => $centro->pivot->porcentaje,
                    'fecha_inicio' => $centro->pivot->fecha_inicio,
                    'fecha_fin' => $centro->pivot->fecha_fin,
                ];
            })
            ->toArray();
    }

    public function agregarCentro()
    {
        $this->validate();

        // Validar que no exista ya
        $existe = $this->empleado->centrosCostoActivos()
            ->where('centro_costo_id', $this->centroCostoId)
            ->exists();

        if ($existe) {
            session()->flash('error', 'Este centro de costo ya está asignado al empleado');
            return;
        }

        // Validar que la suma de porcentajes no supere 100%
        $totalPorcentaje = collect($this->asignaciones)->sum('porcentaje') + $this->porcentaje;
        
        if ($totalPorcentaje > 100) {
            session()->flash('error', "La suma de porcentajes no puede superar 100%. Actual: {$totalPorcentaje}%");
            return;
        }

        try {
            DB::beginTransaction();

            // Asignar centro de costo
            $this->empleado->centrosCosto()->attach($this->centroCostoId, [
                'porcentaje' => $this->porcentaje,
                'fecha_inicio' => now(),
                'fecha_fin' => null,
                'activo' => true,
            ]);

            DB::commit();

            $this->reset(['centroCostoId', 'porcentaje']);
            $this->porcentaje = 100; // Reset al valor por defecto
            $this->cargarDatos();
            
            session()->flash('success', 'Centro de costo asignado correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al asignar centro de costo: ' . $e->getMessage());
        }
    }

    public function eliminarCentro($centroCostoId)
    {
        try {
            // Desactivar la asignación (soft delete)
            $this->empleado->centrosCosto()
                ->updateExistingPivot($centroCostoId, [
                    'fecha_fin' => now(),
                    'activo' => false,
                ]);

            $this->cargarDatos();
            session()->flash('success', 'Centro de costo removido');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar asignación: ' . $e->getMessage());
        }
    }

    public function actualizarPorcentaje($centroCostoId, $nuevoPorcentaje)
    {
        // Validar porcentaje
        if ($nuevoPorcentaje < 0.01 || $nuevoPorcentaje > 100) {
            session()->flash('error', 'El porcentaje debe estar entre 0.01 y 100');
            return;
        }

        // Validar suma total
        $totalPorcentaje = collect($this->asignaciones)
            ->where('id', '!=', $centroCostoId)
            ->sum('porcentaje') + $nuevoPorcentaje;

        if ($totalPorcentaje > 100) {
            session()->flash('error', "La suma de porcentajes no puede superar 100%. Total: {$totalPorcentaje}%");
            return;
        }

        try {
            $this->empleado->centrosCosto()
                ->updateExistingPivot($centroCostoId, [
                    'porcentaje' => $nuevoPorcentaje,
                ]);

            $this->cargarDatos();
            session()->flash('success', 'Porcentaje actualizado');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar porcentaje: ' . $e->getMessage());
        }
    }

    public function getTotalPorcentajeProperty()
    {
        return collect($this->asignaciones)->sum('porcentaje');
    }

    public function getPorcentajeRestanteProperty()
    {
        return 100 - $this->totalPorcentaje;
    }

    public function distribuirEquitativamente()
    {
        $cantidad = count($this->asignaciones);
        
        if ($cantidad === 0) {
            session()->flash('error', 'No hay centros de costo asignados');
            return;
        }

        $porcentajeEquitativo = 100 / $cantidad;

        try {
            DB::beginTransaction();

            foreach ($this->asignaciones as $asignacion) {
                $this->empleado->centrosCosto()
                    ->updateExistingPivot($asignacion['id'], [
                        'porcentaje' => $porcentajeEquitativo,
                    ]);
            }

            DB::commit();

            $this->cargarDatos();
            session()->flash('success', 'Porcentajes distribuidos equitativamente');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al distribuir porcentajes: ' . $e->getMessage());
        }
    }
}