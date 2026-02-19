<?php

namespace App\Http\Controllers\nomina;

use App\Http\Controllers\Controller;
use App\Modules\Nomina\Models\CentroCosto;
use Illuminate\Http\Request;

class CentroCostoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vista = $request->get('vista', 'tabla');
        
        if ($vista === 'arbol') {
            // Vista de árbol: solo centros raíz con sus hijos
            $arbolCentros = CentroCosto::whereNull('padre_id')
                ->with('hijos')
                ->orderBy('codigo')
                ->get();
            
            return view('nomina.livewire.centros-costo.gestion-centros-costo', [
                'centros' => collect([]), // Vacío para la vista de tabla
                'arbolCentros' => $arbolCentros,
            ]);
        }
        
        // Vista de tabla: todos los centros
        $centros = CentroCosto::with('padre')
            ->orderBy('codigo')
            ->get();
        
        return view('nomina.livewire.centros-costo.gestion-centros-costo', [
            'centros' => $centros,
            'arbolCentros' => collect([]), // Vacío para la vista de árbol
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $centrosPadre = CentroCosto::whereNull('padre_id')
            ->orWhere('nivel', '<', 3) // Máximo 3 niveles
            ->orderBy('codigo')
            ->get();
        
        return view('nomina.livewire.centros-costo.create', [
            'centrosPadre' => $centrosPadre,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:centros_costo,codigo',
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'padre_id' => 'nullable|exists:centros_costo,id',
            'activo' => 'boolean',
        ]);

        CentroCosto::create($validated);

        return redirect()->route('nomina.centros-costo.index')
            ->with('success', 'Centro de costo creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CentroCosto $centroCosto)
    {
        $centroCosto->load('padre', 'hijos', 'empleados');
        
        return view('nomina.livewire.centros-costo.show', [
            'centro' => $centroCosto,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CentroCosto $centroCosto)
    {
        $centrosPadre = CentroCosto::whereNull('padre_id')
            ->orWhere('nivel', '<', 3)
            ->where('id', '!=', $centroCosto->id)
            ->orderBy('codigo')
            ->get();
        
        return view('nomina.livewire.centros-costo.edit', [
            'centro' => $centroCosto,
            'centrosPadre' => $centrosPadre,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CentroCosto $centroCosto)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:centros_costo,codigo,' . $centroCosto->id,
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'padre_id' => 'nullable|exists:centros_costo,id',
            'activo' => 'boolean',
        ]);

        $centroCosto->update($validated);

        return redirect()->route('nomina.centros-costo.index')
            ->with('success', 'Centro de costo actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CentroCosto $centroCosto)
    {
        // Verificar si tiene centros hijos
        if ($centroCosto->hijos()->count() > 0) {
            return redirect()->route('nomina.centros-costo.index')
                ->with('error', 'No se puede eliminar un centro de costo que tiene centros hijos.');
        }

        // Verificar si tiene empleados asignados
        if ($centroCosto->empleados()->count() > 0) {
            return redirect()->route('nomina.centros-costo.index')
                ->with('error', 'No se puede eliminar un centro de costo que tiene empleados asignados.');
        }

        $centroCosto->delete();

        return redirect()->route('nomina.centros-costo.index')
            ->with('success', 'Centro de costo eliminado exitosamente.');
    }
}