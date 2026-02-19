<?php

namespace App\Modules\Nomina\Http\Livewire\Provisiones;

use Livewire\Component;
use Livewire\WithPagination;
use App\Modules\Nomina\Models\AsientoContableNomina;
use App\Modules\Nomina\Models\DetalleAsientoNomina;
use App\Modules\Nomina\Models\Nomina;
use Illuminate\Support\Facades\DB;

class AsientosContables extends Component
{
    use WithPagination;

    public $asientoId;
    public $showModal = false;
    public $showDeleteModal = false;
    public $showApprovalModal = false;
    public $modalTitle = 'Detalle de Asiento';
    
    // Detalles del asiento
    public $asiento;
    public $detalles = [];
    
    // Filtros
    public $filterTipo = '';
    public $filterEstado = '';
    public $fechaInicio;
    public $fechaFin;
    public $search = '';
    
    // Aprobación/Anulación
    public $motivoAnulacion;

    public function mount()
    {
        // Establecer fechas por defecto (mes actual)
        $this->fechaInicio = now()->startOfMonth()->format('Y-m-d');
        $this->fechaFin = now()->endOfMonth()->format('Y-m-d');
    }

    public function render()
    {
        $asientos = AsientoContableNomina::query()
            ->with(['nomina', 'detalles'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('numero_asiento', 'like', "%{$this->search}%")
                      ->orWhere('descripcion', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterTipo, fn($q) => $q->where('tipo_asiento', $this->filterTipo))
            ->when($this->filterEstado, fn($q) => $q->where('estado', $this->filterEstado))
            ->when($this->fechaInicio, fn($q) => $q->whereDate('fecha_asiento', '>=', $this->fechaInicio))
            ->when($this->fechaFin, fn($q) => $q->whereDate('fecha_asiento', '<=', $this->fechaFin))
            ->orderByDesc('fecha_asiento')
            ->paginate(20);

        return view('nomina.livewire.provisiones.asientos-contables', [
            'asientos' => $asientos,
        ]);
    }

    public function verDetalle($id)
    {
        $this->asientoId = $id;
        $this->asiento = AsientoContableNomina::with(['nomina', 'detalles.cuentaContable', 'detalles.centroCosto'])
            ->findOrFail($id);
        
        $this->detalles = $this->asiento->detalles->map(function($detalle) {
            return [
                'id' => $detalle->id,
                'cuenta' => $detalle->cuentaContable->codigo . ' - ' . $detalle->cuentaContable->nombre,
                'tercero' => $detalle->tercero,
                'centro_costo' => $detalle->centroCosto ? $detalle->centroCosto->nombre : null,
                'debito' => $detalle->debito,
                'credito' => $detalle->credito,
                'base' => $detalle->base,
            ];
        })->toArray();
        
        $this->modalTitle = 'Asiento Contable: ' . $this->asiento->numero_asiento;
        $this->showModal = true;
    }

    public function aprobar($id = null)
    {
        $asientoId = $id ?? $this->asientoId;
        
        try {
            $asiento = AsientoContableNomina::findOrFail($asientoId);
            
            if ($asiento->estado !== 'borrador') {
                session()->flash('error', 'Solo se pueden aprobar asientos en borrador');
                return;
            }

            // Validar cuadre
            if (!$this->validarCuadre($asiento)) {
                session()->flash('error', 'El asiento no está cuadrado. Débitos ≠ Créditos');
                return;
            }

            DB::beginTransaction();

            $asiento->update([
                'estado' => 'aprobado',
                'aprobado_por' => auth()->id(),
                'fecha_aprobacion' => now(),
            ]);

            DB::commit();

            $this->showModal = false;
            session()->flash('success', 'Asiento aprobado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al aprobar: ' . $e->getMessage());
        }
    }

    public function contabilizar($id = null)
    {
        $asientoId = $id ?? $this->asientoId;
        
        try {
            $asiento = AsientoContableNomina::findOrFail($asientoId);
            
            if ($asiento->estado !== 'aprobado') {
                session()->flash('error', 'Solo se pueden contabilizar asientos aprobados');
                return;
            }

            // Validar cuadre nuevamente
            if (!$this->validarCuadre($asiento)) {
                session()->flash('error', 'El asiento no está cuadrado');
                return;
            }

            DB::beginTransaction();

            $asiento->update([
                'estado' => 'contabilizado',
                'contabilizado_por' => auth()->id(),
                'fecha_contabilizacion' => now(),
            ]);

            // Aquí se podría integrar con el sistema contable
            // $this->enviarASistemaContable($asiento);

            DB::commit();

            $this->showModal = false;
            session()->flash('success', 'Asiento contabilizado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al contabilizar: ' . $e->getMessage());
        }
    }

    public function showAnularDialog($id)
    {
        $this->asientoId = $id;
        $this->motivoAnulacion = '';
        $this->showApprovalModal = true;
    }

    public function anular()
    {
        if (empty($this->motivoAnulacion)) {
            $this->addError('motivoAnulacion', 'Debe indicar el motivo de la anulación');
            return;
        }

        try {
            $asiento = AsientoContableNomina::findOrFail($this->asientoId);
            
            if ($asiento->estado === 'anulado') {
                session()->flash('error', 'El asiento ya está anulado');
                return;
            }

            DB::beginTransaction();

            $asiento->update([
                'estado' => 'anulado',
                'anulado_por' => auth()->id(),
                'fecha_anulacion' => now(),
                'motivo_anulacion' => $this->motivoAnulacion,
            ]);

            DB::commit();

            $this->showApprovalModal = false;
            $this->showModal = false;
            session()->flash('success', 'Asiento anulado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al anular: ' . $e->getMessage());
        }
    }

    public function confirmarDelete($id)
    {
        $asiento = AsientoContableNomina::findOrFail($id);
        
        if ($asiento->estado !== 'borrador') {
            session()->flash('error', 'Solo se pueden eliminar asientos en borrador');
            return;
        }

        $this->asientoId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        try {
            $asiento = AsientoContableNomina::findOrFail($this->asientoId);
            
            // Eliminar detalles
            $asiento->detalles()->delete();
            
            // Eliminar asiento
            $asiento->delete();
            
            $this->showDeleteModal = false;
            session()->flash('success', 'Asiento eliminado exitosamente');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    protected function validarCuadre($asiento)
    {
        $totalDebitos = $asiento->detalles()->sum('debito');
        $totalCreditos = $asiento->detalles()->sum('credito');
        
        // Permitir una diferencia mínima por redondeos (1 peso)
        return abs($totalDebitos - $totalCreditos) < 1;
    }

    public function recalcularTotales($id)
    {
        try {
            $asiento = AsientoContableNomina::findOrFail($id);
            
            if ($asiento->estado !== 'borrador') {
                session()->flash('error', 'Solo se pueden recalcular asientos en borrador');
                return;
            }

            $totalDebitos = $asiento->detalles()->sum('debito');
            $totalCreditos = $asiento->detalles()->sum('credito');

            $asiento->update([
                'total_debitos' => $totalDebitos,
                'total_creditos' => $totalCreditos,
            ]);

            session()->flash('success', 'Totales recalculados');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al recalcular: ' . $e->getMessage());
        }
    }

    public function exportarExcel($id = null)
    {
        try {
            if ($id) {
                $asiento = AsientoContableNomina::with('detalles.cuentaContable')->findOrFail($id);
                $data = $this->prepararDatosExportacion($asiento);
                
                // Aquí iría la lógica de exportación con Maatwebsite\Excel
                // return Excel::download(new AsientoExport($data), "asiento_{$asiento->numero_asiento}.xlsx");
                
                session()->flash('success', 'Exportando asiento...');
            } else {
                // Exportar todos los filtrados
                session()->flash('success', 'Exportando asientos filtrados...');
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error al exportar: ' . $e->getMessage());
        }
    }

    protected function prepararDatosExportacion($asiento)
    {
        return [
            'asiento' => [
                'numero' => $asiento->numero_asiento,
                'fecha' => $asiento->fecha_asiento->format('d/m/Y'),
                'tipo' => $asiento->tipo_asiento,
                'descripcion' => $asiento->descripcion,
                'estado' => $asiento->estado,
            ],
            'detalles' => $asiento->detalles->map(function($detalle) {
                return [
                    'cuenta' => $detalle->cuentaContable->codigo,
                    'nombre_cuenta' => $detalle->cuentaContable->nombre,
                    'tercero' => $detalle->tercero,
                    'debito' => $detalle->debito,
                    'credito' => $detalle->credito,
                ];
            })->toArray(),
            'totales' => [
                'debitos' => $asiento->total_debitos,
                'creditos' => $asiento->total_creditos,
                'cuadrado' => $this->validarCuadre($asiento),
            ],
        ];
    }

    public function duplicar($id)
    {
        try {
            $asientoOriginal = AsientoContableNomina::with('detalles')->findOrFail($id);
            
            DB::beginTransaction();

            // Crear nuevo asiento
            $nuevoAsiento = $asientoOriginal->replicate();
            $nuevoAsiento->numero_asiento = $this->generarNumeroAsiento($asientoOriginal->tipo_asiento);
            $nuevoAsiento->estado = 'borrador';
            $nuevoAsiento->fecha_asiento = now();
            $nuevoAsiento->aprobado_por = null;
            $nuevoAsiento->fecha_aprobacion = null;
            $nuevoAsiento->contabilizado_por = null;
            $nuevoAsiento->fecha_contabilizacion = null;
            $nuevoAsiento->created_by = auth()->id();
            $nuevoAsiento->save();

            // Duplicar detalles
            foreach ($asientoOriginal->detalles as $detalle) {
                $nuevoDetalle = $detalle->replicate();
                $nuevoDetalle->asiento_contable_nomina_id = $nuevoAsiento->id;
                $nuevoDetalle->save();
            }

            DB::commit();

            session()->flash('success', 'Asiento duplicado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al duplicar: ' . $e->getMessage());
        }
    }

    protected function generarNumeroAsiento($tipo)
    {
        $prefijo = match($tipo) {
            'causacion_nomina' => 'CN',
            'pago_nomina' => 'PN',
            'provision_mensual' => 'PM',
            'pago_provision' => 'PP',
            'ajuste' => 'AJ',
            default => 'AS',
        };

        $consecutivo = AsientoContableNomina::where('tipo_asiento', $tipo)
            ->whereYear('fecha_asiento', now()->year)
            ->count() + 1;

        return sprintf('%s-%04d-%04d', $prefijo, now()->year, $consecutivo);
    }

    public function aplicarFiltros()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->filterTipo = '';
        $this->filterEstado = '';
        $this->fechaInicio = now()->startOfMonth()->format('Y-m-d');
        $this->fechaFin = now()->endOfMonth()->format('Y-m-d');
        $this->search = '';
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getEstadisticasProperty()
    {
        return [
            'total' => AsientoContableNomina::count(),
            'borrador' => AsientoContableNomina::where('estado', 'borrador')->count(),
            'aprobados' => AsientoContableNomina::where('estado', 'aprobado')->count(),
            'contabilizados' => AsientoContableNomina::where('estado', 'contabilizado')->count(),
            'anulados' => AsientoContableNomina::where('estado', 'anulado')->count(),
        ];
    }
}