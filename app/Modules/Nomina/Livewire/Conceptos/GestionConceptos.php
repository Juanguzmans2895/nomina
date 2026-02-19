<?php

namespace App\Modules\Nomina\Http\Livewire\Conceptos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Modules\Nomina\Models\ConceptoNomina;
use App\Modules\Nomina\Models\CuentaContable;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class GestionConceptos extends Component
{
    use WithPagination;

    public $conceptoId;
    public $showModal = false;
    public $showDeleteModal = false;
    public $modalTitle = 'Nuevo Concepto';
    
    // Campos del formulario
    public $codigo;
    public $nombre;
    public $descripcion;
    public $clasificacion = 'devengado';
    public $tipo = 'fijo';
    public $prioridad = 100;
    
    // Afectaciones
    public $afecta_base_seguridad_social = true;
    public $afecta_base_parafiscales = true;
    public $afecta_base_retencion = true;
    public $afecta_base_provision_cesantias = true;
    public $afecta_base_provision_vacaciones = true;
    
    // Contabilización
    public $cuenta_debito_id;
    public $cuenta_credito_id;
    
    // Configuración
    public $porcentaje_empleado;
    public $porcentaje_empleador;
    public $valor_fijo;
    public $formula;
    
    // Estado
    public $activo = true;
    public $visible_desprendible = true;
    public $requiere_aprobacion = false;
    
    // Filtros
    public $search = '';
    public $filterClasificacion = '';
    public $filterTipo = '';
    public $filterActivo = '';

    protected function rules()
    {
        return [
            'codigo' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9]+$/',
                Rule::unique('conceptos_nomina', 'codigo')->ignore($this->conceptoId)
            ],
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string|max:500',
            'clasificacion' => 'required|in:devengado,deducido,no_imputable',
            'tipo' => 'required|in:fijo,novedad,calculado',
            'prioridad' => 'required|integer|min:1|max:999',
            'cuenta_debito_id' => 'nullable|exists:cuentas_contables,id',
            'cuenta_credito_id' => 'nullable|exists:cuentas_contables,id',
            'porcentaje_empleado' => 'nullable|numeric|min:0|max:100',
            'porcentaje_empleador' => 'nullable|numeric|min:0|max:100',
            'valor_fijo' => 'nullable|numeric|min:0',
            'formula' => 'nullable|string|max:1000',
        ];
    }

    protected $messages = [
        'codigo.required' => 'El código es obligatorio',
        'codigo.regex' => 'El código solo puede contener letras mayúsculas y números',
        'codigo.unique' => 'Ya existe un concepto con este código',
        'nombre.required' => 'El nombre es obligatorio',
        'clasificacion.required' => 'La clasificación es obligatoria',
        'tipo.required' => 'El tipo es obligatorio',
        'prioridad.required' => 'La prioridad es obligatoria',
    ];

    public function render()
    {
        $conceptos = ConceptoNomina::query()
            ->when($this->search, fn($q) => $q->buscar($this->search))
            ->when($this->filterClasificacion, fn($q) => $q->porClasificacion($this->filterClasificacion))
            ->when($this->filterTipo, fn($q) => $q->porTipo($this->filterTipo))
            ->when($this->filterActivo !== '', fn($q) => $q->where('activo', $this->filterActivo))
            ->with(['cuentaDebito', 'cuentaCredito'])
            ->orderBy('codigo')
            ->paginate(20);

        $cuentasDebito = CuentaContable::whereIn('naturaleza', ['debito', 'ambas'])
            ->activas()
            ->orderBy('codigo')
            ->get();

        $cuentasCredito = CuentaContable::whereIn('naturaleza', ['credito', 'ambas'])
            ->activas()
            ->orderBy('codigo')
            ->get();

        return view('nomina.livewire.conceptos.gestion-conceptos', [
            'conceptos' => $conceptos,
            'cuentasDebito' => $cuentasDebito,
            'cuentasCredito' => $cuentasCredito,
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->modalTitle = 'Nuevo Concepto';
        $this->showModal = true;
    }

    public function edit($id)
    {
        $concepto = ConceptoNomina::findOrFail($id);
        
        $this->conceptoId = $concepto->id;
        $this->modalTitle = 'Editar Concepto';
        
        $this->codigo = $concepto->codigo;
        $this->nombre = $concepto->nombre;
        $this->descripcion = $concepto->descripcion;
        $this->clasificacion = $concepto->clasificacion;
        $this->tipo = $concepto->tipo;
        $this->prioridad = $concepto->prioridad;
        
        $this->afecta_base_seguridad_social = $concepto->afecta_base_seguridad_social;
        $this->afecta_base_parafiscales = $concepto->afecta_base_parafiscales;
        $this->afecta_base_retencion = $concepto->afecta_base_retencion;
        $this->afecta_base_provision_cesantias = $concepto->afecta_base_provision_cesantias;
        $this->afecta_base_provision_vacaciones = $concepto->afecta_base_provision_vacaciones;
        
        $this->cuenta_debito_id = $concepto->cuenta_debito_id;
        $this->cuenta_credito_id = $concepto->cuenta_credito_id;
        
        $this->porcentaje_empleado = $concepto->porcentaje_empleado;
        $this->porcentaje_empleador = $concepto->porcentaje_empleador;
        $this->valor_fijo = $concepto->valor_fijo;
        $this->formula = $concepto->formula;
        
        $this->activo = $concepto->activo;
        $this->visible_desprendible = $concepto->visible_desprendible;
        $this->requiere_aprobacion = $concepto->requiere_aprobacion;
        
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // Validaciones adicionales
        if ($this->clasificacion === 'devengado' && !$this->cuenta_debito_id) {
            $this->addError('cuenta_debito_id', 'Los conceptos devengados requieren cuenta débito');
            return;
        }

        if ($this->clasificacion === 'deducido' && !$this->cuenta_credito_id) {
            $this->addError('cuenta_credito_id', 'Los conceptos deducidos requieren cuenta crédito');
            return;
        }

        if ($this->tipo === 'calculado' && empty($this->formula)) {
            $this->addError('formula', 'Los conceptos calculados deben tener una fórmula');
            return;
        }

        try {
            DB::beginTransaction();

            $data = [
                'codigo' => strtoupper($this->codigo),
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'clasificacion' => $this->clasificacion,
                'tipo' => $this->tipo,
                'prioridad' => $this->prioridad,
                'afecta_base_seguridad_social' => $this->afecta_base_seguridad_social,
                'afecta_base_parafiscales' => $this->afecta_base_parafiscales,
                'afecta_base_retencion' => $this->afecta_base_retencion,
                'afecta_base_provision_cesantias' => $this->afecta_base_provision_cesantias,
                'afecta_base_provision_vacaciones' => $this->afecta_base_provision_vacaciones,
                'cuenta_debito_id' => $this->cuenta_debito_id,
                'cuenta_credito_id' => $this->cuenta_credito_id,
                'porcentaje_empleado' => $this->porcentaje_empleado,
                'porcentaje_empleador' => $this->porcentaje_empleador,
                'valor_fijo' => $this->valor_fijo,
                'formula' => $this->formula,
                'activo' => $this->activo,
                'visible_desprendible' => $this->visible_desprendible,
                'requiere_aprobacion' => $this->requiere_aprobacion,
                'updated_by' => auth()->id(),
            ];

            if ($this->conceptoId) {
                $concepto = ConceptoNomina::findOrFail($this->conceptoId);
                $concepto->update($data);
                $message = 'Concepto actualizado exitosamente';
            } else {
                $data['created_by'] = auth()->id();
                ConceptoNomina::create($data);
                $message = 'Concepto creado exitosamente';
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
        // Verificar si el concepto está en uso
        $concepto = ConceptoNomina::findOrFail($id);
        
        if ($concepto->empleadosConceptoFijo()->exists()) {
            session()->flash('error', 'No se puede eliminar un concepto asignado a empleados');
            return;
        }

        if ($concepto->novedades()->exists()) {
            session()->flash('error', 'No se puede eliminar un concepto con novedades registradas');
            return;
        }

        $this->conceptoId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        try {
            $concepto = ConceptoNomina::findOrFail($this->conceptoId);
            $concepto->delete();
            
            $this->showDeleteModal = false;
            session()->flash('success', 'Concepto eliminado exitosamente');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->conceptoId = null;
        $this->codigo = '';
        $this->nombre = '';
        $this->descripcion = '';
        $this->clasificacion = 'devengado';
        $this->tipo = 'fijo';
        $this->prioridad = 100;
        
        $this->afecta_base_seguridad_social = true;
        $this->afecta_base_parafiscales = true;
        $this->afecta_base_retencion = true;
        $this->afecta_base_provision_cesantias = true;
        $this->afecta_base_provision_vacaciones = true;
        
        $this->cuenta_debito_id = null;
        $this->cuenta_credito_id = null;
        
        $this->porcentaje_empleado = null;
        $this->porcentaje_empleador = null;
        $this->valor_fijo = null;
        $this->formula = '';
        
        $this->activo = true;
        $this->visible_desprendible = true;
        $this->requiere_aprobacion = false;
        
        $this->resetValidation();
    }

    public function toggleActivo($id)
    {
        $concepto = ConceptoNomina::findOrFail($id);
        $concepto->update(['activo' => !$concepto->activo]);
        
        session()->flash('success', 'Estado actualizado');
    }

    public function duplicar($id)
    {
        try {
            $conceptoOriginal = ConceptoNomina::findOrFail($id);
            
            // Generar nuevo código
            $nuevoCodigo = $conceptoOriginal->codigo . '_COPY';
            $contador = 1;
            
            while (ConceptoNomina::where('codigo', $nuevoCodigo)->exists()) {
                $nuevoCodigo = $conceptoOriginal->codigo . '_COPY' . $contador;
                $contador++;
            }
            
            $nuevoConcepto = $conceptoOriginal->replicate();
            $nuevoConcepto->codigo = $nuevoCodigo;
            $nuevoConcepto->nombre = $conceptoOriginal->nombre . ' (Copia)';
            $nuevoConcepto->created_by = auth()->id();
            $nuevoConcepto->save();
            
            session()->flash('success', 'Concepto duplicado exitosamente');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al duplicar: ' . $e->getMessage());
        }
    }

    public function updatedClasificacion()
    {
        // Limpiar cuentas al cambiar clasificación
        if ($this->clasificacion === 'devengado') {
            $this->cuenta_credito_id = null;
        } elseif ($this->clasificacion === 'deducido') {
            $this->cuenta_debito_id = null;
        } else {
            $this->cuenta_debito_id = null;
            $this->cuenta_credito_id = null;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}