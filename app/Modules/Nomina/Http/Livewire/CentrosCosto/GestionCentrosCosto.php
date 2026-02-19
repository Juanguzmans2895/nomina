<?php

namespace App\Modules\Nomina\Http\Livewire\CentrosCosto;

use Livewire\Component;
use Livewire\WithPagination;
use App\Modules\Nomina\Models\CentroCosto;
use App\Modules\Nomina\Models\RubroPresupuestal;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class GestionCentrosCosto extends Component
{
    use WithPagination;

    public $centroCostoId;
    public $showModal = false;
    public $showDeleteModal = false;
    public $modalTitle = 'Nuevo Centro de Costo';
    public $modoVista = 'tabla'; // 'tabla' o 'arbol'
    
    // Campos del formulario
    public $codigo;
    public $nombre;
    public $descripcion;
    public $centro_padre_id;
    public $nivel;
    public $rubro_presupuestal_id;
    public $activo = true;
    public $presupuesto_anual;
    public $responsable;
    
    // Búsqueda y filtros
    public $search = '';
    public $filterActivo = '';
    public $filterNivel = '';
    
    // Árbol jerárquico
    public $arbolCentros = [];

    protected function rules()
    {
        return [
            'codigo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('centros_costo', 'codigo')->ignore($this->centroCostoId)
            ],
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string|max:500',
            'centro_padre_id' => 'nullable|exists:centros_costo,id',
            'nivel' => 'required|integer|min:1|max:5',
            'rubro_presupuestal_id' => 'nullable|exists:rubros_presupuestales,id',
            'activo' => 'boolean',
            'presupuesto_anual' => 'nullable|numeric|min:0',
            'responsable' => 'nullable|string|max:200',
        ];
    }

    protected $messages = [
        'codigo.required' => 'El código es obligatorio',
        'codigo.unique' => 'Ya existe un centro de costo con este código',
        'nombre.required' => 'El nombre es obligatorio',
        'nivel.required' => 'El nivel es obligatorio',
        'nivel.min' => 'El nivel mínimo es 1',
        'nivel.max' => 'El nivel máximo es 5',
    ];

    public function mount()
    {
        $this->cargarArbolCentros();
    }

    public function render()
    {
        $centros = CentroCosto::query()
            ->when($this->search, function($query) {
                $query->buscar($this->search);
            })
            ->when($this->filterActivo !== '', function($query) {
                $query->where('activo', $this->filterActivo);
            })
            ->when($this->filterNivel, function($query) {
                $query->where('nivel', $this->filterNivel);
            })
            ->with(['padre', 'rubroPresupuestal'])
            ->orderBy('codigo')
            ->paginate(20);

        $centrosPadres = CentroCosto::activos()
            ->where('nivel', '<', 5)
            ->orderBy('codigo')
            ->get();

        $rubrosPresupuestales = RubroPresupuestal::activos()
            ->orderBy('codigo')
            ->get();

        return view('nomina.livewire.centros-costo.gestion-centros-costo', [
            'centros' => $centros,
            'centrosPadres' => $centrosPadres,
            'rubrosPresupuestales' => $rubrosPresupuestales,
            'arbolCentros' => $this->arbolCentros,
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->modalTitle = 'Nuevo Centro de Costo';
        $this->showModal = true;
    }

    public function edit($id)
    {
        $centro = CentroCosto::findOrFail($id);
        
        $this->centroCostoId = $centro->id;
        $this->modalTitle = 'Editar Centro de Costo';
        
        $this->codigo = $centro->codigo;
        $this->nombre = $centro->nombre;
        $this->descripcion = $centro->descripcion;
        $this->centro_padre_id = $centro->centro_padre_id;
        $this->nivel = $centro->nivel;
        $this->rubro_presupuestal_id = $centro->rubro_presupuestal_id;
        $this->activo = $centro->activo;
        $this->presupuesto_anual = $centro->presupuesto_anual;
        $this->responsable = $centro->responsable;
        
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // Validar jerarquía
        if ($this->centro_padre_id) {
            $padre = CentroCosto::find($this->centro_padre_id);
            
            if ($padre && $this->nivel <= $padre->nivel) {
                $this->addError('nivel', 'El nivel debe ser mayor que el del centro padre');
                return;
            }

            // Prevenir ciclos
            if ($this->centroCostoId && $this->verificaCiclo($this->centroCostoId, $this->centro_padre_id)) {
                $this->addError('centro_padre_id', 'No se puede crear una jerarquía circular');
                return;
            }
        }

        try {
            DB::beginTransaction();

            $data = [
                'codigo' => $this->codigo,
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'centro_padre_id' => $this->centro_padre_id,
                'nivel' => $this->nivel,
                'rubro_presupuestal_id' => $this->rubro_presupuestal_id,
                'activo' => $this->activo,
                'presupuesto_anual' => $this->presupuesto_anual,
                'responsable' => $this->responsable,
                'updated_by' => auth()->id(),
            ];

            if ($this->centroCostoId) {
                $centro = CentroCosto::findOrFail($this->centroCostoId);
                $centro->update($data);
                $message = 'Centro de costo actualizado exitosamente';
            } else {
                $data['created_by'] = auth()->id();
                CentroCosto::create($data);
                $message = 'Centro de costo creado exitosamente';
            }

            DB::commit();

            $this->showModal = false;
            $this->resetForm();
            $this->cargarArbolCentros();
            session()->flash('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        // Verificar que no tenga hijos
        $centro = CentroCosto::findOrFail($id);
        
        if ($centro->hijos()->exists()) {
            session()->flash('error', 'No se puede eliminar un centro de costo que tiene centros dependientes');
            return;
        }

        // Verificar que no tenga empleados asignados
        if ($centro->empleados()->exists()) {
            session()->flash('error', 'No se puede eliminar un centro de costo con empleados asignados');
            return;
        }

        $this->centroCostoId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        try {
            $centro = CentroCosto::findOrFail($this->centroCostoId);
            $centro->delete();
            
            $this->showDeleteModal = false;
            $this->cargarArbolCentros();
            session()->flash('success', 'Centro de costo eliminado exitosamente');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->centroCostoId = null;
        $this->codigo = '';
        $this->nombre = '';
        $this->descripcion = '';
        $this->centro_padre_id = null;
        $this->nivel = 1;
        $this->rubro_presupuestal_id = null;
        $this->activo = true;
        $this->presupuesto_anual = null;
        $this->responsable = '';
        
        $this->resetValidation();
    }

    public function cambiarModoVista($modo)
    {
        $this->modoVista = $modo;
        
        if ($modo === 'arbol') {
            $this->cargarArbolCentros();
        }
    }

    protected function cargarArbolCentros()
    {
        $centrosRaiz = CentroCosto::whereNull('centro_padre_id')
            ->activos()
            ->orderBy('codigo')
            ->get();

        $this->arbolCentros = $centrosRaiz->map(function($centro) {
            return $this->construirNodoArbol($centro);
        })->toArray();
    }

    protected function construirNodoArbol($centro, $profundidad = 0)
    {
        $hijos = $centro->hijos()->activos()->orderBy('codigo')->get();

        return [
            'id' => $centro->id,
            'codigo' => $centro->codigo,
            'nombre' => $centro->nombre,
            'nivel' => $centro->nivel,
            'profundidad' => $profundidad,
            'presupuesto' => $centro->presupuesto_anual,
            'responsable' => $centro->responsable,
            'tiene_hijos' => $hijos->isNotEmpty(),
            'hijos' => $hijos->map(function($hijo) use ($profundidad) {
                return $this->construirNodoArbol($hijo, $profundidad + 1);
            })->toArray(),
        ];
    }

    protected function verificaCiclo($centroId, $padreId)
    {
        if ($centroId == $padreId) {
            return true;
        }

        $padre = CentroCosto::find($padreId);
        
        while ($padre && $padre->centro_padre_id) {
            if ($padre->centro_padre_id == $centroId) {
                return true;
            }
            $padre = $padre->padre;
        }

        return false;
    }

    public function updatedCentroPadreId($value)
    {
        if ($value) {
            $padre = CentroCosto::find($value);
            if ($padre) {
                $this->nivel = $padre->nivel + 1;
            }
        } else {
            $this->nivel = 1;
        }
    }

    public function exportarExcel()
    {
        // Lógica para exportar a Excel
        session()->flash('info', 'Exportando centros de costo...');
    }

    public function generarPresupuesto()
    {
        // Lógica para generar distribución de presupuesto
        session()->flash('info', 'Generando distribución de presupuesto...');
    }
}