<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Modules\Nomina\Models\PeriodoNomina;
use App\Modules\Nomina\Models\TipoNomina;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PeriodoController extends Controller
{
    /**
     * Listar períodos
     */
    public function index(Request $request)
    {
        $query = PeriodoNomina::query();
        
        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        
        if ($request->filled('tipo_nomina_id')) {
            $query->where('tipo_nomina_id', $request->tipo_nomina_id);
        }
        
        if ($request->filled('anio')) {
            $query->where('anio', $request->anio);
        }
        
        $periodos = $query->orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->paginate(15);
        
        $tiposNomina = TipoNomina::activos()->get();
        $estados = ['abierto', 'cerrado', 'liquidado'];
        
        return view('nomina.periodos.index', compact('periodos', 'tiposNomina', 'estados'));
    }

    /**
     * Formulario crear período
     */
    public function create()
    {
        $tiposNomina = TipoNomina::activos()->get();
        
        // Sugerir próximo período (mes siguiente)
        $ultimoPeriodo = PeriodoNomina::orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->first();
        
        $siguiente = Carbon::now()->addMonth();
        if ($ultimoPeriodo) {
            $siguiente = Carbon::createFromDate($ultimoPeriodo->anio, $ultimoPeriodo->mes, 1)->addMonth();
        }
        
        $sugerenciaMes = $siguiente->month;
        $sugerenciaAnio = $siguiente->year;
        
        return view('nomina.periodos.create', compact(
            'tiposNomina',
            'sugerenciaMes',
            'sugerenciaAnio'
        ));
    }

    /**
     * Guardar período
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_nomina_id' => 'required|exists:tipos_nomina,id',
            'mes' => 'required|integer|min:1|max:12',
            'anio' => 'required|integer|min:2020|max:2050',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'observaciones' => 'nullable|string',
        ]);

        // Generar código (YYYYMM)
        $codigo = $validated['anio'] . str_pad($validated['mes'], 2, '0', STR_PAD_LEFT);

        // Verificar que no exista este período
        $existe = PeriodoNomina::where('codigo', $codigo)
            ->where('tipo_nomina_id', $validated['tipo_nomina_id'])
            ->exists();

        if ($existe) {
            return redirect()->back()
                ->with('error', "El período {$validated['mes']}/{$validated['anio']} ya existe");
        }

        $periodo = PeriodoNomina::create([
            'tipo_nomina_id' => $validated['tipo_nomina_id'],
            'codigo' => $codigo,
            'nombre' => $this->generarNombre($validated['mes'], $validated['anio']),
            'mes' => $validated['mes'],
            'anio' => $validated['anio'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'estado' => 'abierto',
            'activo' => true,
            'observaciones' => $validated['observaciones'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('nomina.periodos.index')
            ->with('success', "Período {$periodo->nombre} creado exitosamente");
    }

    /**
     * Editar período
     */
    public function edit(PeriodoNomina $periodo)
    {
        $tiposNomina = TipoNomina::activos()->get();
        
        return view('nomina.periodos.edit', compact('periodo', 'tiposNomina'));
    }

    /**
     * Actualizar período
     */
    public function update(Request $request, PeriodoNomina $periodo)
    {
        // No permitir editar períodos cerrados
        if ($periodo->estado === 'cerrado') {
            return redirect()->back()
                ->with('error', 'No se puede editar un período cerrado');
        }

        $validated = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'observaciones' => 'nullable|string',
        ]);

        $periodo->update([
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'observaciones' => $validated['observaciones'],
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('nomina.periodos.index')
            ->with('success', 'Período actualizado exitosamente');
    }

    /**
     * Eliminar período
     */
    public function destroy(PeriodoNomina $periodo)
    {
        // No permitir eliminar si tiene nóminas asociadas
        if ($periodo->nominas()->exists()) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar un período que tiene nóminas asociadas');
        }

        $periodo->delete();

        return redirect()->route('nomina.periodos.index')
            ->with('success', 'Período eliminado exitosamente');
    }

    /**
     * Cerrar período
     */
    public function cerrar(Request $request, PeriodoNomina $periodo)
    {
        if ($periodo->estado !== 'abierto') {
            return redirect()->back()
                ->with('error', 'Solo pueden cerrarse períodos en estado abierto');
        }

        $periodo->update([
            'estado' => 'cerrado',
            'fecha_cierre' => now(),
            'cerrado_by' => auth()->id(),
        ]);

        return redirect()->route('nomina.periodos.index')
            ->with('success', "Período {$periodo->nombre} cerrado exitosamente");
    }

    /**
     * Reabrir período
     */
    public function reabrir(PeriodoNomina $periodo)
    {
        if ($periodo->estado !== 'cerrado') {
            return redirect()->back()
                ->with('error', 'Solo pueden reabrirse períodos cerrados');
        }

        $periodo->update([
            'estado' => 'abierto',
            'fecha_cierre' => null,
            'cerrado_by' => null,
        ]);

        return redirect()->route('nomina.periodos.index')
            ->with('success', "Período {$periodo->nombre} reabierto exitosamente");
    }

    /**
     * API: Períodos abiertos
     */
    public function abiertos()
    {
        $periodos = PeriodoNomina::where('estado', 'abierto')
            ->where('activo', true)
            ->orderBy('anio', 'desc')
            ->orderBy('mes', 'desc')
            ->get();
        
        return response()->json($periodos);
    }

    /**
     * API: Validar período
     */
    public function validar(PeriodoNomina $periodo)
    {
        $valido = $periodo->estado === 'abierto' && $periodo->activo;
        
        $mensaje = $valido 
            ? 'Período válido para liquidación'
            : 'El período está cerrado o inactivo';
        
        return response()->json([
            'valido' => $valido,
            'mensaje' => $mensaje,
            'periodo' => $periodo,
        ]);
    }

    /**
     * Generar nombre legible del período
     */
    private function generarNombre(int $mes, int $anio): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        
        return "{$meses[$mes]} {$anio}";
    }
}