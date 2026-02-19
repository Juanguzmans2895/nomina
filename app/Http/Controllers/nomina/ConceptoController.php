<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Nomina\Models\ConceptoNomina;
use App\Modules\Nomina\Enums\ClasificacionConcepto;
use App\Modules\Nomina\Enums\TipoConcepto;

class ConceptoController extends Controller
{
    /**
     * Listado de conceptos
     */
    public function index(Request $request)
    {
        $query = ConceptoNomina::query();
        
        // Filtro de búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                  ->orWhere('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }
        
        // Filtro de clasificación
        if ($request->filled('clasificacion')) {
            $query->where('clasificacion', $request->clasificacion);
        }
        
        $conceptos = $query->orderBy('codigo')->paginate(15);
        
        return view('nomina.livewire.conceptos.gestion-conceptos', compact('conceptos'));
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        return view('nomina.livewire.conceptos.create');
    }

    /**
     * Guardar nuevo concepto
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:conceptos_nomina,codigo',
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'clasificacion' => 'required|in:DEVENGADO,DEDUCIDO,NO_IMPUTABLE',
            'tipo' => 'required|in:FIJO,VARIABLE,CALCULADO,NOVEDAD',
            'base_salarial' => 'nullable|boolean',
            'afecta_prestaciones' => 'nullable|boolean',
            'afecta_seguridad_social' => 'nullable|boolean',
            'afecta_parafiscales' => 'nullable|boolean',
            'aplica_retencion' => 'nullable|boolean',
            'porcentaje' => 'nullable|numeric|min:0|max:100',
            'valor_fijo' => 'nullable|numeric|min:0',
            'formula' => 'nullable|string',
            'visible_colilla' => 'nullable|boolean',
            'orden_colilla' => 'nullable|integer|min:0',
            'agrupador' => 'nullable|string|max:100',
            'activo' => 'nullable|boolean',
        ]);
        
        // Convertir strings a booleanos
        $validated['base_salarial'] = $request->has('base_salarial');
        $validated['afecta_prestaciones'] = $request->has('afecta_prestaciones');
        $validated['afecta_seguridad_social'] = $request->has('afecta_seguridad_social');
        $validated['afecta_parafiscales'] = $request->has('afecta_parafiscales');
        $validated['aplica_retencion'] = $request->has('aplica_retencion');
        $validated['visible_colilla'] = $request->has('visible_colilla');
        $validated['activo'] = $request->has('activo') ? true : true; // Por defecto activo
        $validated['sistema'] = false; // Los creados manualmente no son de sistema
        
        $validated['created_by'] = auth()->id();
        
        ConceptoNomina::create($validated);
        
        return redirect()->route('nomina.conceptos.index')
            ->with('success', 'Concepto creado exitosamente');
    }

    /**
     * Formulario de edición
     */
    public function edit(ConceptoNomina $concepto)
    {
        return view('nomina.livewire.conceptos.edit', compact('concepto'));
    }

    /**
     * Actualizar concepto
     */
    public function update(Request $request, ConceptoNomina $concepto)
    {
        // Los conceptos de sistema no se pueden editar ciertos campos
        $rules = [
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'porcentaje' => 'nullable|numeric|min:0|max:100',
            'valor_fijo' => 'nullable|numeric|min:0',
            'formula' => 'nullable|string',
            'visible_colilla' => 'nullable|boolean',
            'orden_colilla' => 'nullable|integer|min:0',
            'agrupador' => 'nullable|string|max:100',
            'activo' => 'nullable|boolean',
        ];
        
        // Si no es de sistema, permitir editar más campos
        if (!$concepto->sistema) {
            $rules['codigo'] = 'required|string|max:50|unique:conceptos_nomina,codigo,' . $concepto->id;
            $rules['clasificacion'] = 'required|in:DEVENGADO,DEDUCIDO,NO_IMPUTABLE';
            $rules['tipo'] = 'required|in:FIJO,VARIABLE,CALCULADO,NOVEDAD';
            $rules['base_salarial'] = 'nullable|boolean';
            $rules['afecta_prestaciones'] = 'nullable|boolean';
            $rules['afecta_seguridad_social'] = 'nullable|boolean';
            $rules['afecta_parafiscales'] = 'nullable|boolean';
            $rules['aplica_retencion'] = 'nullable|boolean';
        }
        
        $validated = $request->validate($rules);
        
        // Convertir checkboxes a booleanos
        if (!$concepto->sistema) {
            $validated['base_salarial'] = $request->has('base_salarial');
            $validated['afecta_prestaciones'] = $request->has('afecta_prestaciones');
            $validated['afecta_seguridad_social'] = $request->has('afecta_seguridad_social');
            $validated['afecta_parafiscales'] = $request->has('afecta_parafiscales');
            $validated['aplica_retencion'] = $request->has('aplica_retencion');
        }
        
        $validated['visible_colilla'] = $request->has('visible_colilla');
        $validated['activo'] = $request->has('activo');
        $validated['updated_by'] = auth()->id();
        
        $concepto->update($validated);
        
        return redirect()->route('nomina.conceptos.index')
            ->with('success', 'Concepto actualizado exitosamente');
    }

    /**
     * Eliminar concepto
     */
    public function destroy(ConceptoNomina $concepto)
    {
        // No permitir eliminar conceptos de sistema
        if ($concepto->sistema) {
            return redirect()->back()
                ->with('error', 'No se pueden eliminar conceptos del sistema');
        }
        
        // Verificar que no esté en uso
        // TODO: Agregar verificación de uso en nóminas
        
        $concepto->delete();
        
        return redirect()->route('nomina.conceptos.index')
            ->with('success', 'Concepto eliminado exitosamente');
    }
}