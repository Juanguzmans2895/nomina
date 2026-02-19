<?php

namespace App\Modules\Nomina\Http\Livewire\Contratos;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Modules\Nomina\Models\Contrato;
use App\Modules\Nomina\Models\PagoContrato;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RegistroPagoContrato extends Component
{
    use WithPagination, WithFileUploads;

    public $contratoId;
    public $contrato;
    public $showModal = false;
    public $pagoId;
    
    // Datos del pago
    public $numero_pago;
    public $fecha_pago;
    public $periodo_inicio;
    public $periodo_fin;
    public $valor_bruto;
    public $valor_retencion;
    public $valor_estampilla;
    public $otros_descuentos;
    public $valor_neto;
    public $observaciones;
    
    // Documentos
    public $soporte_pago;
    public $certificacion_cumplimiento;
    
    // Estado
    public $estado = 'pendiente';
    
    // Filtros
    public $filterEstado = '';

    protected function rules()
    {
        return [
            'numero_pago' => 'required|string|max:50',
            'fecha_pago' => 'required|date',
            'periodo_inicio' => 'nullable|date',
            'periodo_fin' => 'nullable|date|after_or_equal:periodo_inicio',
            'valor_bruto' => 'required|numeric|min:0',
            'valor_retencion' => 'nullable|numeric|min:0',
            'valor_estampilla' => 'nullable|numeric|min:0',
            'otros_descuentos' => 'nullable|numeric|min:0',
            'valor_neto' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
            'soporte_pago' => 'nullable|file|max:10240|mimes:pdf',
            'certificacion_cumplimiento' => 'nullable|file|max:10240|mimes:pdf',
            'estado' => 'required|in:pendiente,aprobado,pagado,anulado',
        ];
    }

    protected $messages = [
        'numero_pago.required' => 'El número de pago es obligatorio',
        'fecha_pago.required' => 'La fecha de pago es obligatoria',
        'periodo_fin.after_or_equal' => 'El período fin debe ser posterior al período inicio',
        'valor_bruto.required' => 'El valor bruto es obligatorio',
        'valor_bruto.min' => 'El valor bruto debe ser mayor a cero',
        'valor_neto.required' => 'El valor neto es obligatorio',
        'soporte_pago.max' => 'El archivo no debe superar 10MB',
        'soporte_pago.mimes' => 'El soporte debe ser un archivo PDF',
    ];

    public function mount($contratoId)
    {
        $this->contratoId = $contratoId;
        $this->contrato = Contrato::with('pagos')->findOrFail($contratoId);
        $this->generarNumeroPago();
    }

    public function render()
    {
        $pagos = PagoContrato::where('contrato_id', $this->contratoId)
            ->when($this->filterEstado, fn($q) => $q->where('estado', $this->filterEstado))
            ->orderByDesc('fecha_pago')
            ->paginate(10);

        return view('nomina.livewire.contratos.registro-pago-contrato', [
            'pagos' => $pagos,
            'contrato' => $this->contrato,
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->generarNumeroPago();
        $this->calcularValoresDefecto();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $pago = PagoContrato::findOrFail($id);
        
        if ($pago->estado === 'pagado') {
            session()->flash('error', 'No se puede editar un pago que ya fue procesado');
            return;
        }
        
        $this->pagoId = $pago->id;
        $this->numero_pago = $pago->numero_pago;
        $this->fecha_pago = $pago->fecha_pago->format('Y-m-d');
        $this->periodo_inicio = $pago->periodo_inicio?->format('Y-m-d');
        $this->periodo_fin = $pago->periodo_fin?->format('Y-m-d');
        $this->valor_bruto = $pago->valor_bruto;
        $this->valor_retencion = $pago->valor_retencion;
        $this->valor_estampilla = $pago->valor_estampilla;
        $this->otros_descuentos = $pago->otros_descuentos;
        $this->valor_neto = $pago->valor_neto;
        $this->observaciones = $pago->observaciones;
        $this->estado = $pago->estado;
        
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // Validar saldo disponible
        $totalPagado = PagoContrato::where('contrato_id', $this->contratoId)
            ->where('estado', '!=', 'anulado')
            ->when($this->pagoId, fn($q) => $q->where('id', '!=', $this->pagoId))
            ->sum('valor_bruto');

        $saldoDisponible = $this->contrato->valor_total - $totalPagado;

        if ($this->valor_bruto > $saldoDisponible) {
            $this->addError('valor_bruto', "El saldo disponible del contrato es: $" . number_format($saldoDisponible, 2));
            return;
        }

        try {
            DB::beginTransaction();

            $data = [
                'contrato_id' => $this->contratoId,
                'numero_pago' => $this->numero_pago,
                'fecha_pago' => $this->fecha_pago,
                'periodo_inicio' => $this->periodo_inicio,
                'periodo_fin' => $this->periodo_fin,
                'valor_bruto' => $this->valor_bruto,
                'valor_retencion' => $this->valor_retencion ?? 0,
                'valor_estampilla' => $this->valor_estampilla ?? 0,
                'otros_descuentos' => $this->otros_descuentos ?? 0,
                'valor_neto' => $this->valor_neto,
                'observaciones' => $this->observaciones,
                'estado' => $this->estado,
                'updated_by' => auth()->id(),
            ];

            // Guardar archivos
            if ($this->soporte_pago) {
                $data['soporte_pago_path'] = $this->soporte_pago->store('contratos/pagos', 'public');
            }

            if ($this->certificacion_cumplimiento) {
                $data['certificacion_cumplimiento_path'] = $this->certificacion_cumplimiento->store('contratos/certificaciones', 'public');
            }

            if ($this->pagoId) {
                $pago = PagoContrato::findOrFail($this->pagoId);
                $pago->update($data);
                $message = 'Pago actualizado exitosamente';
            } else {
                $data['created_by'] = auth()->id();
                PagoContrato::create($data);
                $message = 'Pago registrado exitosamente';
            }

            // Actualizar saldo del contrato
            $this->actualizarSaldoContrato();

            DB::commit();

            $this->showModal = false;
            $this->resetForm();
            $this->contrato->refresh();
            session()->flash('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function aprobar($id)
    {
        try {
            $pago = PagoContrato::findOrFail($id);
            
            if ($pago->estado !== 'pendiente') {
                session()->flash('error', 'Solo se pueden aprobar pagos pendientes');
                return;
            }

            $pago->update([
                'estado' => 'aprobado',
                'aprobado_por' => auth()->id(),
                'fecha_aprobacion' => now(),
            ]);

            session()->flash('success', 'Pago aprobado exitosamente');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al aprobar: ' . $e->getMessage());
        }
    }

    public function marcarComoPagado($id)
    {
        try {
            $pago = PagoContrato::findOrFail($id);
            
            if ($pago->estado !== 'aprobado') {
                session()->flash('error', 'El pago debe estar aprobado para marcarlo como pagado');
                return;
            }

            $pago->update([
                'estado' => 'pagado',
                'pagado_por' => auth()->id(),
                'fecha_pago_real' => now(),
            ]);

            $this->actualizarSaldoContrato();
            $this->contrato->refresh();

            session()->flash('success', 'Pago marcado como pagado');

        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function anular($id)
    {
        try {
            $pago = PagoContrato::findOrFail($id);
            
            if ($pago->estado === 'pagado') {
                session()->flash('error', 'No se pueden anular pagos que ya fueron procesados');
                return;
            }

            $pago->update([
                'estado' => 'anulado',
                'anulado_por' => auth()->id(),
                'fecha_anulacion' => now(),
            ]);

            $this->actualizarSaldoContrato();
            $this->contrato->refresh();

            session()->flash('success', 'Pago anulado');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al anular: ' . $e->getMessage());
        }
    }

    protected function generarNumeroPago()
    {
        $ultimoPago = PagoContrato::where('contrato_id', $this->contratoId)
            ->orderByDesc('id')
            ->first();

        $consecutivo = $ultimoPago ? (intval(substr($ultimoPago->numero_pago, -3)) + 1) : 1;
        
        $this->numero_pago = sprintf(
            'PAGO-%s-%03d',
            $this->contrato->numero_contrato,
            $consecutivo
        );
    }

    protected function calcularValoresDefecto()
    {
        $this->fecha_pago = now()->format('Y-m-d');
        
        // Calcular retención si aplica
        if ($this->contrato->aplica_retencion_fuente) {
            $valorMensual = $this->contrato->valor_mensual ?? ($this->contrato->valor_total / ($this->contrato->plazo_dias / 30));
            $this->valor_bruto = $valorMensual;
            $this->valor_retencion = $valorMensual * ($this->contrato->porcentaje_retencion_fuente / 100);
            
            if ($this->contrato->aplica_estampilla) {
                $this->valor_estampilla = $valorMensual * ($this->contrato->porcentaje_estampilla / 100);
            }
            
            $this->calcularValorNeto();
        }
    }

    public function calcularValorNeto()
    {
        $this->valor_neto = $this->valor_bruto 
            - ($this->valor_retencion ?? 0) 
            - ($this->valor_estampilla ?? 0) 
            - ($this->otros_descuentos ?? 0);
    }

    protected function actualizarSaldoContrato()
    {
        $totalPagado = PagoContrato::where('contrato_id', $this->contratoId)
            ->where('estado', '!=', 'anulado')
            ->sum('valor_bruto');

        $this->contrato->update([
            'saldo_pendiente' => $this->contrato->valor_total - $totalPagado,
        ]);
    }

    public function resetForm()
    {
        $this->pagoId = null;
        $this->numero_pago = '';
        $this->fecha_pago = '';
        $this->periodo_inicio = '';
        $this->periodo_fin = '';
        $this->valor_bruto = 0;
        $this->valor_retencion = 0;
        $this->valor_estampilla = 0;
        $this->otros_descuentos = 0;
        $this->valor_neto = 0;
        $this->observaciones = '';
        $this->soporte_pago = null;
        $this->certificacion_cumplimiento = null;
        $this->estado = 'pendiente';
        
        $this->resetValidation();
    }

    public function updatedValorBruto()
    {
        $this->calcularValorNeto();
    }

    public function updatedValorRetencion()
    {
        $this->calcularValorNeto();
    }

    public function updatedValorEstampilla()
    {
        $this->calcularValorNeto();
    }

    public function updatedOtrosDescuentos()
    {
        $this->calcularValorNeto();
    }
}