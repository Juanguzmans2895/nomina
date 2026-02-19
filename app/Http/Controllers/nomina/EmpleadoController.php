<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\CentroCosto;
use App\Modules\Nomina\Models\ConceptoNomina;
use App\Modules\Nomina\Models\EmpleadoCentroCosto;
use App\Modules\Nomina\Models\EmpleadoConceptoFijo;
use App\Http\Requests\Nomina\EmpleadoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmpleadoController extends Controller
{
    /**
     * Listado de empleados
     */
    public function index(Request $request)
    {
        $query = Empleado::query();
        
        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('primer_nombre', 'like', "%{$search}%")
                  ->orWhere('primer_apellido', 'like', "%{$search}%")
                  ->orWhere('numero_documento', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        
        // Filtro por dependencia
        if ($request->filled('dependencia')) {
            $query->where('dependencia', $request->dependencia);
        }
        
        $empleados = $query->orderBy('primer_apellido')
            ->orderBy('primer_nombre')
            ->paginate(20);
        
        $dependencias = Empleado::select('dependencia')
            ->distinct()
            ->whereNotNull('dependencia')
            ->pluck('dependencia');
        
        return view('nomina.livewire.empleados.gestion-empleados', compact('empleados', 'dependencias'));
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        return view('nomina.livewire.empleados.create');
    }

    /**
     * Guardar nuevo empleado
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'primer_nombre' => 'required|string|max:100',
            'segundo_nombre' => 'nullable|string|max:100',
            'primer_apellido' => 'required|string|max:100',
            'segundo_apellido' => 'nullable|string|max:100',
            'tipo_documento' => 'required|string|max:10',
            'numero_documento' => 'required|string|max:20|unique:empleados',
            'genero' => 'required|in:M,F,O',
            'fecha_nacimiento' => 'nullable|date',
            'email' => 'nullable|email|unique:empleados',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'cargo' => 'required|string|max:100',
            'dependencia' => 'nullable|string|max:100',
            'fecha_ingreso' => 'required|date',
            'tipo_contrato' => 'required|string',
            'salario_basico' => 'required|numeric|min:0',
            'clase_riesgo' => 'required|numeric',
            'estado' => 'nullable|string|in:activo,inactivo',
            'eps' => 'nullable|string|max:100',
            'fondo_pension' => 'nullable|string|max:100',
            'arl' => 'nullable|string|max:100',
            'caja_compensacion' => 'nullable|string|max:100',
        ]);
        
        $validated['estado'] = $validated['estado'] ?? 'activo';
        
        // ✅ GENERAR CÓDIGO AUTOMÁTICAMENTE
        $validated['codigo_empleado'] = $this->generarCodigoEmpleado();
        
        Empleado::create($validated);
        
        return redirect()->route('nomina.empleados.index')
            ->with('success', 'Empleado creado exitosamente');
    }

    /**
     * Generar código de empleado automático
     */
    private function generarCodigoEmpleado()
    {
        $ultimoEmpleado = \App\Modules\Nomina\Models\Empleado::orderBy('id', 'desc')->first();
        
        if ($ultimoEmpleado && $ultimoEmpleado->codigo_empleado) {
            preg_match('/(\d+)$/', $ultimoEmpleado->codigo_empleado, $matches);
            $ultimoNumero = isset($matches[1]) ? intval($matches[1]) : 0;
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }
        
        return 'EMP-' . date('Y') . '-' . str_pad($nuevoNumero, 4, '0', STR_PAD_LEFT);
    }


    /**
     * Formulario de edición
     */
    public function edit(Empleado $empleado)
    {
        return view('nomina.livewire.empleados.empleados-edit', compact('empleado'));
    }

    /**
     * Actualizar empleado
     */
    public function update(EmpleadoRequest $request, Empleado $empleado)
    {
        try {
            DB::beginTransaction();
            
            $empleado->update(array_merge(
                $request->validated(),
                ['updated_by' => auth()->id()]
            ));
            
            DB::commit();
            
            return redirect()
                ->route('nomina.empleados.index')
                ->with('success', 'Empleado actualizado exitosamente');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar empleado: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar empleado
     */
    public function destroy(Empleado $empleado)
    {
        try {
            $empleado->delete();
            
            return redirect()
                ->route('nomina.empleados.index')
                ->with('success', 'Empleado eliminado exitosamente');
                
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al eliminar empleado: ' . $e->getMessage());
        }
    }

    /**
     * Asignación de centros de costo
     */
    public function centrosCosto(Empleado $empleado)
    {
        $empleado->load('centrosCostoActivos.centroCosto');
        $centrosDisponibles = CentroCosto::activos()->get();
        $porcentajeAsignado = $empleado->centrosCostoActivos->sum('porcentaje');
        $porcentajeDisponible = 100 - $porcentajeAsignado;
        
        return view('nomina.empleados.centros-costo', compact(
            'empleado',
            'centrosDisponibles',
            'porcentajeAsignado',
            'porcentajeDisponible'
        ));
    }

    /**
     * Guardar asignación de centros de costo
     */
    public function guardarCentrosCosto(Request $request, Empleado $empleado)
    {
        $request->validate([
            'asignaciones' => 'required|array',
            'asignaciones.*.centro_costo_id' => 'required|exists:centros_costo,id',
            'asignaciones.*.porcentaje' => 'required|numeric|min:0.01|max:100',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Validar que la suma sea 100%
            $totalPorcentaje = collect($request->asignaciones)->sum('porcentaje');
            
            if (abs($totalPorcentaje - 100) > 0.01) {
                throw new \Exception('La suma de porcentajes debe ser exactamente 100%');
            }
            
            // Desactivar asignaciones anteriores
            EmpleadoCentroCosto::where('empleado_id', $empleado->id)
                ->where('activo', true)
                ->update([
                    'activo' => false,
                    'fecha_fin' => now(),
                ]);
            
            // Crear nuevas asignaciones
            foreach ($request->asignaciones as $asignacion) {
                EmpleadoCentroCosto::create([
                    'empleado_id' => $empleado->id,
                    'centro_costo_id' => $asignacion['centro_costo_id'],
                    'porcentaje' => $asignacion['porcentaje'],
                    'fecha_inicio' => now(),
                    'activo' => true,
                    'created_by' => auth()->id(),
                ]);
            }
            
            DB::commit();
            
            return back()->with('success', 'Centros de costo asignados correctamente');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Conceptos fijos del empleado
     */
    public function conceptosFijos(Empleado $empleado)
    {
        $empleado->load('conceptosFijosActivos.concepto');
        
        $conceptosDisponibles = ConceptoNomina::where('tipo', 'fijo')
            ->activos()
            ->get()
            ->groupBy('clasificacion');
        
        $totales = [
            'devengados' => $empleado->conceptosFijosActivos
                ->where('concepto.clasificacion', 'devengado')
                ->sum(function($cf) use ($empleado) {
                    return $cf->valor ?: ($empleado->salario_basico * $cf->porcentaje / 100);
                }),
            'deducciones' => $empleado->conceptosFijosActivos
                ->where('concepto.clasificacion', 'deduccion')
                ->sum(function($cf) use ($empleado) {
                    return $cf->valor ?: ($empleado->salario_basico * $cf->porcentaje / 100);
                }),
        ];
        
        $totales['neto'] = $empleado->salario_basico + $totales['devengados'] - $totales['deducciones'];
        
        return view('nomina.empleados.conceptos-fijos', compact(
            'empleado',
            'conceptosDisponibles',
            'totales'
        ));
    }

    /**
     * Guardar conceptos fijos
     */
    public function guardarConceptosFijos(Request $request, Empleado $empleado)
    {
        $request->validate([
            'concepto_nomina_id' => 'required|exists:conceptos_nomina,id',
            'tipo_calculo' => 'required|in:valor,porcentaje',
            'valor' => 'required_if:tipo_calculo,valor|numeric|min:0',
            'porcentaje' => 'required_if:tipo_calculo,porcentaje|numeric|min:0|max:100',
            'observaciones' => 'nullable|string|max:500',
        ]);
        
        try {
            EmpleadoConceptoFijo::create([
                'empleado_id' => $empleado->id,
                'concepto_nomina_id' => $request->concepto_nomina_id,
                'valor' => $request->tipo_calculo === 'valor' ? $request->valor : null,
                'porcentaje' => $request->tipo_calculo === 'porcentaje' ? $request->porcentaje : null,
                'observaciones' => $request->observaciones,
                'fecha_inicio' => now(),
                'activo' => true,
                'created_by' => auth()->id(),
            ]);
            
            return back()->with('success', 'Concepto fijo agregado correctamente');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error al agregar concepto: ' . $e->getMessage());
        }
    }

    /**
     * API: Buscar empleados
     */
    public function buscar(Request $request)
    {
        $term = $request->get('q', '');
        
        $empleados = Empleado::where('estado', 'activo')
            ->where(function($query) use ($term) {
                $query->where('primer_nombre', 'like', "%{$term}%")
                      ->orWhere('primer_apellido', 'like', "%{$term}%")
                      ->orWhere('numero_documento', 'like', "%{$term}%");
            })
            ->limit(20)
            ->get(['id', 'numero_documento', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido'])
            ->map(function($emp) {
                return [
                    'id' => $emp->id,
                    'text' => $emp->nombre_completo . ' - ' . $emp->numero_documento,
                    'documento' => $emp->numero_documento,
                    'nombre' => $emp->nombre_completo,
                ];
            });
        
        return response()->json($empleados);
    }

    /**
     * API: Obtener datos del empleado
     */
    public function obtenerDatos(Empleado $empleado)
    {
        return response()->json([
            'id' => $empleado->id,
            'numero_documento' => $empleado->numero_documento,
            'nombre_completo' => $empleado->nombre_completo,
            'salario_basico' => $empleado->salario_basico,
            'cargo' => $empleado->cargo,
            'dependencia' => $empleado->dependencia,
            'eps' => $empleado->eps,
            'fondo_pension' => $empleado->fondo_pension,
            'arl' => $empleado->arl,
        ]);
    }
}