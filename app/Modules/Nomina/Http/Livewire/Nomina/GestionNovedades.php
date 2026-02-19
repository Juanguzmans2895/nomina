<?php

namespace App\Modules\Nomina\Http\Livewire\Nomina;

use Livewire\Component;
use Livewire\WithPagination;
use App\Modules\Nomina\Models\NovedadNomina;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\ConceptoNomina;
use App\Modules\Nomina\Models\PeriodoNomina;
use Illuminate\Support\Facades\DB;

class GestionNovedades extends Component
{
    use WithPagination;

    public $novedadId;
    public $showModal = false;
    public $showDeleteModal = false;
    public $showApprovalModal = false;
    public $modalTitle = 'Nueva Novedad';
    
    // Campos del formulario
    public $empleado_id;
    public $concepto_nomina_id;
    public $periodo_nomina_id;
    public $fecha_novedad;
    public $cantidad = 1;
    public $valor_unitario = 0;
    public $valor_total = 0;
    public $observaciones;
    public $requiere_aprobacion = false;
    public $estado = 'pendiente';
    
    // Aprobación
    public $motivoRechazo;
    
    // Filtros
    public $search = '';
    public $filterPeriodo = '';
    public $filterEstado = '';
    public $filterConcepto = '';
    public $filterEmpleado = '';

    protected function rules()
    {
        return [
            'empleado_id' => 'required|exists:empleados,id',
            'concepto_nomina_id' => 'required|exists:conceptos_nomina,id',
            'periodo_nomina_id' => 'required|exists:periodos_nomina,id',
            'fecha_novedad' => 'required|date',
            'cantidad' => 'required|numeric|min:0',
            'valor_unitario' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
        ];
    }

    protected $messages = [
        'empleado_id.required' => 'El empleado es obligatorio',
        'concepto_nomina_id.required' => 'El concepto es obligatorio',
        'periodo_nomina_id.required' => 'El período es obligatorio',
        'fecha_novedad.required' => 'La fecha es obligatoria',
        'cantidad.required' => 'La cantidad es obligatoria',
        'cantidad.min' => 'La cantidad debe ser mayor o igual a cero',
        'valor_unitario.required' => 'El valor unitario es obligatorio',
        'valor_unitario.min' => 'El valor debe ser mayor o igual a cero',
    ];

    public function render()
    {
        $novedades = NovedadNomina::query()
            ->with(['empleado', 'concepto', 'periodo'])
            ->when($this->search, function($query) {
                $query->whereHas('empleado', function($q) {
                    $q->where('primer_nombre', 'like', "%{$this->search}%")
                      ->orWhere('primer_apellido', 'like', "%{$this->search}%")
                      ->orWhere('numero_documento', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterPeriodo, fn($q) => $q->where('periodo_nomina_id', $this->filterPeriodo))
            ->when($this->filterEstado, fn($q) => $q->where('estado', $this->filterEstado))
            ->when($this->filterConcepto, fn($q) => $q->where('concepto_nomina_id', $this->filterConcepto))
            ->when($this->filterEmpleado, fn($q) => $q->where('empleado_id', $this->filterEmpleado))
            ->orderByDesc('fecha_novedad')
            ->paginate(20);

        $empleados = Empleado::activos()->orderBy('primer_apellido')->get();
        $conceptos = ConceptoNomina::activos()->where('tipo', 'novedad')->orderBy('codigo')->get();
        $periodos = PeriodoNomina::abiertos()->orderByDesc('anio')->orderByDesc('mes')->get();

        return view('nomina.livewire.nomina.gestion-novedades', [
            'novedades' => $novedades,
            'empleados' => $empleados,
            'conceptos' => $conceptos,
            'periodos' => $periodos,
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->modalTitle = 'Nueva Novedad';
        
        // Establecer período actual por defecto
        $periodoActual = PeriodoNomina::abiertos()
            ->where('anio', now()->year)
            ->where('mes', now()->month)
            ->first();
        
        if ($periodoActual) {
            $this->periodo_nomina_id = $periodoActual->id;
        }
        
        $this->fecha_novedad = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function edit($id)
    {
        $novedad = NovedadNomina::findOrFail($id);
        
        if ($novedad->procesada) {
            session()->flash('error', 'No se puede editar una novedad que ya fue procesada');
            return;
        }

        if ($novedad->estado === 'aprobada') {
            session()->flash('error', 'No se puede editar una novedad aprobada');
            return;
        }
        
        $this->novedadId = $novedad->id;
        $this->modalTitle = 'Editar Novedad';
        
        $this->empleado_id = $novedad->empleado_id;
        $this->concepto_nomina_id = $novedad->concepto_nomina_id;
        $this->periodo_nomina_id = $novedad->periodo_nomina_id;
        $this->fecha_novedad = $novedad->fecha_novedad->format('Y-m-d');
        $this->cantidad = $novedad->cantidad;
        $this->valor_unitario = $novedad->valor_unitario;
        $this->valor_total = $novedad->valor_total;
        $this->observaciones = $novedad->observaciones;
        $this->requiere_aprobacion = $novedad->requiere_aprobacion;
        $this->estado = $novedad->estado;
        
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // Validaciones adicionales
        $concepto = ConceptoNomina::find($this->concepto_nomina_id);
        if (!$concepto || !$concepto->activo) {
            $this->addError('concepto_nomina_id', 'El concepto seleccionado no está activo');
            return;
        }

        if ($concepto->tipo !== 'novedad') {
            $this->addError('concepto_nomina_id', 'Solo se pueden crear novedades con conceptos tipo "novedad"');
            return;
        }

        $empleado = Empleado::find($this->empleado_id);
        if (!$empleado || $empleado->estado !== 'activo') {
            $this->addError('empleado_id', 'Solo se pueden crear novedades para empleados activos');
            return;
        }

        $periodo = PeriodoNomina::find($this->periodo_nomina_id);
        if (!$periodo || $periodo->estado === 'cerrado') {
            $this->addError('periodo_nomina_id', 'El período seleccionado está cerrado');
            return;
        }

        try {
            DB::beginTransaction();

            // Calcular valor total
            $this->valor_total = $this->cantidad * $this->valor_unitario;

            $data = [
                'empleado_id' => $this->empleado_id,
                'concepto_nomina_id' => $this->concepto_nomina_id,
                'periodo_nomina_id' => $this->periodo_nomina_id,
                'fecha_novedad' => $this->fecha_novedad,
                'cantidad' => $this->cantidad,
                'valor_unitario' => $this->valor_unitario,
                'valor_total' => $this->valor_total,
                'observaciones' => $this->observaciones,
                'requiere_aprobacion' => $concepto->requiere_aprobacion,
                'estado' => $concepto->requiere_aprobacion ? 'pendiente' : 'aprobada',
                'procesada' => false,
                'updated_by' => auth()->id(),
            ];

            if ($this->novedadId) {
                $novedad = NovedadNomina::findOrFail($this->novedadId);
                $novedad->update($data);
                $message = 'Novedad actualizada exitosamente';
            } else {
                $data['created_by'] = auth()->id();
                NovedadNomina::create($data);
                $message = 'Novedad creada exitosamente';
            }

            DB::commit();

            $this->showModal = false;
            $this->resetForm();
            session()->flash('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $novedad = NovedadNomina::findOrFail($id);
        
        if ($novedad->procesada) {
            session()->flash('error', 'No se puede eliminar una novedad que ya fue procesada');
            return;
        }

        $this->novedadId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        try {
            $novedad = NovedadNomina::findOrFail($this->novedadId);
            $novedad->delete();
            
            $this->showDeleteModal = false;
            session()->flash('success', 'Novedad eliminada exitosamente');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function showApprovalDialog($id)
    {
        $this->novedadId = $id;
        $this->motivoRechazo = '';
        $this->showApprovalModal = true;
    }

    public function aprobar($id = null)
    {
        $novedadId = $id ?? $this->novedadId;
        
        try {
            $novedad = NovedadNomina::findOrFail($novedadId);
            
            if ($novedad->estado !== 'pendiente') {
                session()->flash('error', 'Solo se pueden aprobar novedades pendientes');
                return;
            }

            $novedad->update([
                'estado' => 'aprobada',
                'aprobada_por' => auth()->id(),
                'fecha_aprobacion' => now(),
            ]);

            $this->showApprovalModal = false;
            session()->flash('success', 'Novedad aprobada exitosamente');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al aprobar: ' . $e->getMessage());
        }
    }

    public function rechazar()
    {
        if (empty($this->motivoRechazo)) {
            $this->addError('motivoRechazo', 'Debe indicar el motivo del rechazo');
            return;
        }

        try {
            $novedad = NovedadNomina::findOrFail($this->novedadId);
            
            if ($novedad->estado !== 'pendiente') {
                session()->flash('error', 'Solo se pueden rechazar novedades pendientes');
                return;
            }

            $novedad->update([
                'estado' => 'rechazada',
                'rechazada_por' => auth()->id(),
                'fecha_rechazo' => now(),
                'motivo_rechazo' => $this->motivoRechazo,
            ]);

            $this->showApprovalModal = false;
            session()->flash('success', 'Novedad rechazada');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al rechazar: ' . $e->getMessage());
        }
    }

    public function aprobarMasivo()
    {
        try {
            $novedadesPendientes = NovedadNomina::where('estado', 'pendiente')
                ->when($this->filterPeriodo, fn($q) => $q->where('periodo_nomina_id', $this->filterPeriodo))
                ->get();

            $contador = 0;
            foreach ($novedadesPendientes as $novedad) {
                $novedad->update([
                    'estado' => 'aprobada',
                    'aprobada_por' => auth()->id(),
                    'fecha_aprobacion' => now(),
                ]);
                $contador++;
            }

            session()->flash('success', "Se aprobaron {$contador} novedades");

        } catch (\Exception $e) {
            session()->flash('error', 'Error al aprobar masivamente: ' . $e->getMessage());
        }
    }

    public function duplicar($id)
    {
        try {
            $novedadOriginal = NovedadNomina::findOrFail($id);
            
            $nuevaNovedad = $novedadOriginal->replicate();
            $nuevaNovedad->estado = 'pendiente';
            $nuevaNovedad->procesada = false;
            $nuevaNovedad->fecha_novedad = now();
            $nuevaNovedad->created_by = auth()->id();
            $nuevaNovedad->save();

            session()->flash('success', 'Novedad duplicada exitosamente');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al duplicar: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->novedadId = null;
        $this->empleado_id = null;
        $this->concepto_nomina_id = null;
        $this->periodo_nomina_id = null;
        $this->fecha_novedad = '';
        $this->cantidad = 1;
        $this->valor_unitario = 0;
        $this->valor_total = 0;
        $this->observaciones = '';
        $this->requiere_aprobacion = false;
        $this->estado = 'pendiente';
        
        $this->resetValidation();
    }

    public function updatedCantidad()
    {
        $this->calcularValorTotal();
    }

    public function updatedValorUnitario()
    {
        $this->calcularValorTotal();
    }

    protected function calcularValorTotal()
    {
        $this->valor_total = $this->cantidad * $this->valor_unitario;
    }

    public function updatedConceptoNominaId($value)
    {
        if ($value) {
            $concepto = ConceptoNomina::find($value);
            if ($concepto && $concepto->valor_fijo) {
                $this->valor_unitario = $concepto->valor_fijo;
                $this->calcularValorTotal();
            }
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function exportarExcel()
    {
        // Lógica para exportar a Excel
        session()->flash('info', 'Exportando novedades...');
    }
}