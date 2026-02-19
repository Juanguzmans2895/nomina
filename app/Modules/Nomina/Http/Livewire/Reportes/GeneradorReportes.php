<?php
namespace App\Modules\Nomina\Http\Livewire\Reportes;

use Livewire\Component;
use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Services\Reportes\DesprendibleService;
use App\Modules\Nomina\Services\Reportes\ConsolidadosService;

class GeneradorReportes extends Component
{
    public $tipoReporte = 'desprendibles';
    public $nominaId;
    public $formato = 'pdf';
    
    protected $queryString = ['tipoReporte'];

    public function render()
    {
        $nominas = Nomina::whereIn('estado', ['aprobada', 'pagada'])
            ->orderByDesc('fecha_inicio')
            ->limit(50)
            ->get();

        return view('nomina.livewire.reportes.generador-reportes', [
            'nominas' => $nominas,
        ]);
    }

    public function generarReporte()
    {
        $this->validate([
            'nominaId' => 'required|exists:nominas,id',
        ]);

        $nomina = Nomina::findOrFail($this->nominaId);

        switch ($this->tipoReporte) {
            case 'desprendibles':
                return $this->generarDesprendibles($nomina);
            case 'consolidado':
                return $this->generarConsolidado($nomina);
            case 'pila':
                return $this->generarPILA($nomina);
        }
    }

    protected function generarDesprendibles($nomina)
    {
        $service = new DesprendibleService();
        
        if ($this->formato === 'pdf') {
            return response()->streamDownload(function() use ($service, $nomina) {
                echo $service->generarDesprendiblesMasivos($nomina);
            }, "desprendibles_{$nomina->numero_nomina}.pdf");
        }
    }
}