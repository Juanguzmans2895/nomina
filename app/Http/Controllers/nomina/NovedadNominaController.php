<?php

namespace App\Http\Controllers\Nomina;

use App\Modules\Nomina\Models\NovedadNomina;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\ConceptoNomina;
use App\Modules\Nomina\Models\PeriodoNomina;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NovedadNominaController extends Controller
{
    /**
     * Listado de novedades con filtros CORREGIDOS
     */
    public function index(Request $request)
    {
        $query = NovedadNomina::with(['empleado', 'concepto', 'periodo']);

        // ══════════════════════════════════════════════════════════
        // FILTRO DE ESTADO - CORRECCIÓN CRÍTICA
        // ══════════════════════════════════════════════════════════
        if ($request->filled('estado')) {
            $estado = $request->estado;
            
            // Si buscan "procesada", traducir a "aplicada"
            if ($estado === 'procesada') {
                $query->where('estado', 'aplicada');
            } else {
                $query->where('estado', $estado);
            }
        }

        // Filtro por empleado
        if ($request->filled('empleado')) {
            $query->whereHas('empleado', function ($q) use ($request) {
                $q->where('nombre_completo', 'like', '%' . $request->empleado . '%')
                  ->orWhere('primer_nombre', 'like', '%' . $request->empleado . '%')
                  ->orWhere('primer_apellido', 'like', '%' . $request->empleado . '%')
                  ->orWhere('numero_documento', 'like', '%' . $request->empleado . '%');
            });
        }

        // Filtro por concepto
        if ($request->filled('concepto')) {
            $query->whereHas('concepto', function ($q) use ($request) {
                $q->where('codigo', 'like', '%' . $request->concepto . '%')
                  ->orWhere('nombre', 'like', '%' . $request->concepto . '%');
            });
        }

        // Filtro por período
        if ($request->filled('periodo')) {
            if ($request->periodo === 'actual') {
                $periodoActual = PeriodoNomina::where('codigo', now()->format('Ym'))->first();
                if ($periodoActual) {
                    $query->where('periodo_id', $periodoActual->id);
                }
            } else {
                $query->where('periodo_id', $request->periodo);
            }
        }

        // Ordenar por más recientes
        $query->orderBy('created_at', 'desc');

        // Paginar
        $novedades = $query->paginate(20);

        // ══════════════════════════════════════════════════════════
        // CONTADORES CORREGIDOS
        // ══════════════════════════════════════════════════════════
        $totalNovedades = NovedadNomina::count();
        $pendientes = NovedadNomina::where('estado', 'pendiente')->count();
        $aprobadas = NovedadNomina::where('estado', 'aprobada')->count(); // ← Aprobadas en lugar de Aplicadas

        // ══════════════════════════════════════════════════════════
        // RUTA CORRECTA DE LA VISTA
        // ══════════════════════════════════════════════════════════
        return view('nomina.livewire.nomina.gestion-novedades', compact(
            'novedades',
            'totalNovedades',
            'pendientes',
            'aprobadas'
        ));
    }

    /**
     * Formulario para crear novedad
     */
    public function create()
    {
        $empleados = Empleado::where('estado', 'activo')
            ->orderBy('primer_nombre')
            ->get();
        
        $conceptos = ConceptoNomina::where('activo', true)
            ->orderBy('codigo')
            ->get();
        
        // ══════════════════════════════════════════════════════════
        // CORRECCIÓN: SOLO MOSTRAR PERÍODOS ABIERTOS
        // ══════════════════════════════════════════════════════════
        $periodos = PeriodoNomina::where('estado', 'abierto')
            ->where('activo', true)
            ->orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->get();

        if ($periodos->isEmpty()) {
            return redirect()->route('nomina.novedades.index')
                ->with('warning', 'No hay períodos abiertos. Cree un nuevo período para poder registrar novedades.');
        }

        return view('nomina.novedades.create', compact('empleados', 'conceptos', 'periodos'));
    }

    /**
     * Guardar nueva novedad
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'concepto_id' => 'required|exists:conceptos_nomina,id',
            'periodo_id' => 'required|exists:periodos_nomina,id',
            'fecha' => 'required|date',
            'cantidad' => 'required|numeric|min:0',
            'valor_unitario' => 'nullable|numeric|min:0',
            'valor_total' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        $novedad = NovedadNomina::create(array_merge($validated, [
            'estado' => 'pendiente',
            'created_by' => auth()->id(),
        ]));

        return redirect()->route('nomina.novedades.index')
            ->with('success', 'Novedad creada exitosamente');
    }

    /**
     * Editar novedad
     */
    public function edit($id)
    {
        $novedad = NovedadNomina::with(['empleado', 'concepto', 'periodo'])->findOrFail($id);
        
        $empleados = Empleado::where('estado', 'activo')->orderBy('primer_nombre')->get();
        $conceptos = ConceptoNomina::where('activo', true)->orderBy('codigo')->get();
        
        // ══════════════════════════════════════════════════════════
        // SOLO MOSTRAR PERÍODOS ABIERTOS (o el actual si está cerrado)
        // ══════════════════════════════════════════════════════════
        $periodos = PeriodoNomina::where(function($q) {
            $q->where('estado', 'abierto')
              ->where('activo', true)
              ->orWhere('id', PeriodoNomina::where('estado', 'abierto')->value('id'));
        })
        ->orderBy('anio', 'desc')
        ->orderBy('mes', 'desc')
        ->get();

        return view('nomina.novedades.edit', compact('novedad', 'empleados', 'conceptos', 'periodos'));
    }

    /**
     * Actualizar novedad
     */
    public function update(Request $request, $id)
    {
        $novedad = NovedadNomina::findOrFail($id);

        // Solo permitir editar si está pendiente
        if ($novedad->estado !== 'pendiente') {
            return redirect()->back()
                ->with('error', 'No se puede editar una novedad que ya fue procesada');
        }

        $validated = $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'concepto_id' => 'required|exists:conceptos_nomina,id',
            'periodo_id' => 'required|exists:periodos_nomina,id',
            'fecha' => 'required|date',
            'cantidad' => 'required|numeric|min:0',
            'valor_unitario' => 'nullable|numeric|min:0',
            'valor_total' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        $novedad->update(array_merge($validated, [
            'updated_by' => auth()->id(),
        ]));

        return redirect()->route('nomina.novedades.index')
            ->with('success', 'Novedad actualizada exitosamente');
    }

    /**
     * Eliminar novedad
     */
    public function destroy($id)
    {
        $novedad = NovedadNomina::findOrFail($id);

        // Solo permitir eliminar si está pendiente
        if ($novedad->estado !== 'pendiente') {
            return redirect()->back()
                ->with('error', 'No se puede eliminar una novedad que ya fue procesada');
        }

        $novedad->delete();

        return redirect()->route('nomina.novedades.index')
            ->with('success', 'Novedad eliminada exitosamente');
    }

    /**
     * Aprobar novedad
     */
    public function aprobar($id)
    {
        try {
            $novedad = NovedadNomina::findOrFail($id);

            // Verificar que esté en estado pendiente
            if ($novedad->estado !== 'pendiente') {
                return redirect()->back()
                    ->with('error', 'Solo se pueden aprobar novedades en estado pendiente');
            }

            $aprobado = $novedad->aprobar(auth()->id());
            
            if ($aprobado) {
                return redirect()->back()
                    ->with('success', 'Novedad aprobada exitosamente');
            } else {
                return redirect()->back()
                    ->with('error', 'No se pudo aprobar la novedad. Intente nuevamente.');
            }
        } catch (\Exception $e) {
            \Log::error('Error al aprobar novedad: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al aprobar la novedad: ' . $e->getMessage());
        }
    }

    /**
     * Rechazar novedad
     */
    public function rechazar(Request $request, $id)
    {
        $request->validate([
            'motivo_rechazo' => 'required|string|max:500',
        ]);

        try {
            $novedad = NovedadNomina::findOrFail($id);

            // Verificar que esté en estado pendiente
            if ($novedad->estado !== 'pendiente') {
                return redirect()->back()
                    ->with('error', 'Solo se pueden rechazar novedades en estado pendiente');
            }

            $rechazado = $novedad->rechazar($request->motivo_rechazo, auth()->id());
            
            if ($rechazado) {
                return redirect()->back()
                    ->with('success', 'Novedad rechazada exitosamente');
            } else {
                return redirect()->back()
                    ->with('error', 'No se pudo rechazar la novedad. Intente nuevamente.');
            }
        } catch (\Exception $e) {
            \Log::error('Error al rechazar novedad: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al rechazar la novedad: ' . $e->getMessage());
        }
    }
}