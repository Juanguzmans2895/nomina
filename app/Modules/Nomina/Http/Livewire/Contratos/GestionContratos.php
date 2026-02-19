<?php
namespace App\Modules\Nomina\Http\Livewire\Contratos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Modules\Nomina\Models\Contrato;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\CentroCosto;

class GestionContratos extends Component
{
    use WithPagination;

    public $contratoId;
    public $showModal = false;
    
    // Datos del contrato
    public $numero_contrato;
    public $tipo_contrato = 'prestacion_servicios';
    public $fecha_inicio;
    public $fecha_fin;
    public $valor_total;
    public $valor_mensual;
    
    // Contratista
    public $numero_documento_contratista;
    public $nombre_contratista;
    public $email_contratista;
    
    // Supervisor y Centro
    public $supervisor_id;
    public $centro_costo_id;
    
    // Retenciones
    public $aplica_retencion_fuente = true;
    public $porcentaje_retencion_fuente = 10.00;
    
    // Estado
    public $estado = 'borrador';
    
    // Filtros
    public $search = '';
    public $filterEstado = '';

    protected function rules()
    {
        return [
            'numero_contrato' => 'required|unique:contratos,numero_contrato,' . $this->contratoId,
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'valor_total' => 'required|numeric|min:0',
            'numero_documento_contratista' => 'required',
            'nombre_contratista' => 'required',
            'supervisor_id' => 'nullable|exists:empleados,id',
            'centro_costo_id' => 'nullable|exists:centros_costo,id',
        ];
    }

    public function render()
    {
        $contratos = Contrato::query()
            ->when($this->search, fn($q) => $q->buscar($this->search))
            ->when($this->filterEstado, fn($q) => $q->where('estado', $this->filterEstado))
            ->with(['supervisor', 'centroCosto'])
            ->orderByDesc('created_at')
            ->paginate(15);

        $supervisores = Empleado::activos()->orderBy('primer_apellido')->get();
        $centrosCosto = CentroCosto::activos()->orderBy('codigo')->get();

        return view('nomina.livewire.contratos.gestion-contratos', [
            'contratos' => $contratos,
            'supervisores' => $supervisores,
            'centrosCosto' => $centrosCosto,
        ]);
    }

    public function save()
    {
        $this->validate();

        $data = [
            'numero_contrato' => $this->numero_contrato,
            'tipo_contrato' => $this->tipo_contrato,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'valor_total' => $this->valor_total,
            'valor_mensual' => $this->valor_mensual,
            'numero_documento_contratista' => $this->numero_documento_contratista,
            'nombre_contratista' => $this->nombre_contratista,
            'email_contratista' => $this->email_contratista,
            'supervisor_id' => $this->supervisor_id,
            'centro_costo_id' => $this->centro_costo_id,
            'aplica_retencion_fuente' => $this->aplica_retencion_fuente,
            'porcentaje_retencion_fuente' => $this->porcentaje_retencion_fuente,
            'estado' => $this->estado,
            'saldo_pendiente' => $this->valor_total,
        ];

        if ($this->contratoId) {
            Contrato::findOrFail($this->contratoId)->update($data);
        } else {
            Contrato::create($data);
        }

        $this->showModal = false;
        session()->flash('success', 'Contrato guardado exitosamente');
    }
}