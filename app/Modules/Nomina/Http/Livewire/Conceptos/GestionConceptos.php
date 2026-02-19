<?php
namespace App\Modules\Nomina\Http\Livewire\Conceptos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Modules\Nomina\Models\ConceptoNomina;
use App\Modules\Nomina\Models\CuentaContable;

class GestionConceptos extends Component
{
    use WithPagination;

    public $conceptoId;
    public $showModal = false;
    public $modalTitle = 'Nuevo Concepto';
    
    // Campos
    public $codigo;
    public $nombre;
    public $clasificacion = 'devengado';
    public $tipo = 'fijo';
    public $afecta_base_seguridad_social = true;
    public $afecta_base_parafiscales = true;
    public $cuenta_debito_id;
    public $cuenta_credito_id;
    public $activo = true;
    
    // Filtros
    public $search = '';
    public $filterClasificacion = '';
    public $filterTipo = '';

    protected function rules()
    {
        return [
            'codigo' => 'required|string|max:20|unique:conceptos_nomina,codigo,' . $this->conceptoId,
            'nombre' => 'required|string|max:200',
            'clasificacion' => 'required|in:devengado,deducido,no_imputable',
            'tipo' => 'required|in:fijo,novedad,calculado',
            'afecta_base_seguridad_social' => 'boolean',
            'afecta_base_parafiscales' => 'boolean',
            'cuenta_debito_id' => 'nullable|exists:cuentas_contables,id',
            'cuenta_credito_id' => 'nullable|exists:cuentas_contables,id',
            'activo' => 'boolean',
        ];
    }

    public function render()
    {
        $conceptos = ConceptoNomina::query()
            ->when($this->search, fn($q) => $q->buscar($this->search))
            ->when($this->filterClasificacion, fn($q) => $q->porClasificacion($this->filterClasificacion))
            ->when($this->filterTipo, fn($q) => $q->porTipo($this->filterTipo))
            ->orderBy('codigo')
            ->paginate(20);

        $cuentasContables = CuentaContable::orderBy('codigo')->get();

        return view('nomina.livewire.conceptos.gestion-conceptos', [
            'conceptos' => $conceptos,
            'cuentasContables' => $cuentasContables,
        ]);
    }

    public function save()
    {
        $this->validate();

        $data = [
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'clasificacion' => $this->clasificacion,
            'tipo' => $this->tipo,
            'afecta_base_seguridad_social' => $this->afecta_base_seguridad_social,
            'afecta_base_parafiscales' => $this->afecta_base_parafiscales,
            'cuenta_debito_id' => $this->cuenta_debito_id,
            'cuenta_credito_id' => $this->cuenta_credito_id,
            'activo' => $this->activo,
        ];

        if ($this->conceptoId) {
            ConceptoNomina::findOrFail($this->conceptoId)->update($data);
            $message = 'Concepto actualizado exitosamente';
        } else {
            ConceptoNomina::create($data);
            $message = 'Concepto creado exitosamente';
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', $message);
    }
    
    // Métodos adicionales: create(), edit($id), delete(), resetForm()
}