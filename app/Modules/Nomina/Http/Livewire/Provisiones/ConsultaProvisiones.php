<?php
namespace App\Modules\Nomina\Http\Livewire\Provisiones;

use Livewire\Component;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\ProvisionEmpleado;
use App\Modules\Nomina\Services\ProvisionesService;

class ConsultaProvisiones extends Component
{
    public $empleadoId;
    public $anio;
    public $reporte;

    public function mount()
    {
        $this->anio = now()->year;
    }

    public function render()
    {
        $empleados = Empleado::activos()->orderBy('primer_apellido')->get();
        
        return view('nomina.livewire.provisiones.consulta-provisiones', [
            'empleados' => $empleados,
        ]);
    }

    public function consultar()
    {
        $this->validate([
            'empleadoId' => 'required|exists:empleados,id',
            'anio' => 'required|integer|min:2020|max:' . (now()->year + 1),
        ]);

        $provisionesService = new ProvisionesService();
        $this->reporte = $provisionesService->getReporteEmpleado($this->empleadoId, $this->anio);
    }
}