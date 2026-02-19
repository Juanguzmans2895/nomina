<?php

namespace App\Modules\Nomina\Http\Livewire\Empleados;

use Livewire\Component;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\ConceptoNomina;
use Illuminate\Support\Facades\DB;

class ConceptosFijos extends Component
{
    public $empleadoId;
    public $empleado;
    public $conceptosDisponibles;
    public $conceptosFijos = [];
    
    // Nuevo concepto fijo
    public $conceptoId;
    public $tipoValor = 'valor'; // 'valor' o 'porcentaje'
    public $valor;
    public $porcentaje;
    public $observaciones;

    protected function rules()
    {
        $rules = [
            'conceptoId' => 'required|exists:conceptos_nomina,id',
            'observaciones' => 'nullable|string|max:500',
        ];

        if ($this->tipoValor === 'valor') {
            $rules['valor'] = 'required|numeric|min:0';
        } else {
            $rules['porcentaje'] = 'required|numeric|min:0|max:100';
        }

        return $rules;
    }

    public function mount($empleadoId)
    {
        $this->empleadoId = $empleadoId;
        $this->empleado = Empleado::findOrFail($empleadoId);
        $this->cargarDatos();
    }

    public function render()
    {
        return view('nomina.livewire.empleados.conceptos-fijos');
    }

    public function cargarDatos()
    {
        // Cargar conceptos disponibles (devengados y deducidos)
        $this->conceptosDisponibles = ConceptoNomina::activos()
            ->whereIn('clasificacion', ['devengado', 'deducido'])
            ->where('tipo', 'fijo')
            ->orderBy('codigo')
            ->get();

        // Cargar conceptos fijos actuales
        $this->conceptosFijos = $this->empleado->conceptosFijosActivos()
            ->withPivot('valor', 'porcentaje', 'observaciones', 'fecha_inicio', 'fecha_fin')
            ->get()
            ->map(function($concepto) {
                $valorCalculado = $concepto->pivot->porcentaje 
                    ? ($this->empleado->salario_basico * $concepto->pivot->porcentaje / 100)
                    : $concepto->pivot->valor;

                return [
                    'id' => $concepto->id,
                    'codigo' => $concepto->codigo,
                    'nombre' => $concepto->nombre,
                    'clasificacion' => $concepto->clasificacion,
                    'valor' => $concepto->pivot->valor,
                    'porcentaje' => $concepto->pivot->porcentaje,
                    'valor_calculado' => $valorCalculado,
                    'observaciones' => $concepto->pivot->observaciones,
                    'fecha_inicio' => $concepto->pivot->fecha_inicio,
                ];
            })
            ->toArray();
    }

    public function agregarConcepto()
    {
        $this->validate();

        // Verificar que no exista ya
        $existe = $this->empleado->conceptosFijosActivos()
            ->where('concepto_nomina_id', $this->conceptoId)
            ->exists();

        if ($existe) {
            session()->flash('error', 'Este concepto ya está asignado al empleado');
            return;
        }

        try {
            DB::beginTransaction();

            $data = [
                'fecha_inicio' => now(),
                'fecha_fin' => null,
                'activo' => true,
                'observaciones' => $this->observaciones,
            ];

            if ($this->tipoValor === 'valor') {
                $data['valor'] = $this->valor;
                $data['porcentaje'] = null;
            } else {
                $data['valor'] = null;
                $data['porcentaje'] = $this->porcentaje;
            }

            $this->empleado->conceptosFijos()->attach($this->conceptoId, $data);

            DB::commit();

            $this->reset(['conceptoId', 'valor', 'porcentaje', 'observaciones']);
            $this->tipoValor = 'valor';
            $this->cargarDatos();

            session()->flash('success', 'Concepto fijo asignado correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al asignar concepto: ' . $e->getMessage());
        }
    }

    public function eliminarConcepto($conceptoId)
    {
        try {
            // Desactivar el concepto (soft delete)
            $this->empleado->conceptosFijos()
                ->updateExistingPivot($conceptoId, [
                    'fecha_fin' => now(),
                    'activo' => false,
                ]);

            $this->cargarDatos();
            session()->flash('success', 'Concepto fijo removido');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar concepto: ' . $e->getMessage());
        }
    }

    public function actualizarValor($conceptoId, $nuevoValor, $tipo)
    {
        if ($nuevoValor < 0) {
            session()->flash('error', 'El valor debe ser mayor o igual a cero');
            return;
        }

        if ($tipo === 'porcentaje' && $nuevoValor > 100) {
            session()->flash('error', 'El porcentaje no puede superar 100%');
            return;
        }

        try {
            $data = [];
            
            if ($tipo === 'valor') {
                $data['valor'] = $nuevoValor;
                $data['porcentaje'] = null;
            } else {
                $data['valor'] = null;
                $data['porcentaje'] = $nuevoValor;
            }

            $this->empleado->conceptosFijos()
                ->updateExistingPivot($conceptoId, $data);

            $this->cargarDatos();
            session()->flash('success', 'Valor actualizado');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar valor: ' . $e->getMessage());
        }
    }

    public function getTotalDevengadosProperty()
    {
        return collect($this->conceptosFijos)
            ->where('clasificacion', 'devengado')
            ->sum('valor_calculado');
    }

    public function getTotalDeducidosProperty()
    {
        return collect($this->conceptosFijos)
            ->where('clasificacion', 'deducido')
            ->sum('valor_calculado');
    }

    public function getNetoEstimadoProperty()
    {
        return $this->empleado->salario_basico + $this->totalDevengados - $this->totalDeducidos;
    }

    public function updatedTipoValor()
    {
        $this->reset(['valor', 'porcentaje']);
    }
}