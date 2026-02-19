<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class ConfiguracionController extends Controller
{
    /**
     * Vista de configuración
     */
    public function index()
    {
        $configuracion = [
            'smlv' => config('nomina.smlv.valor_actual', 1300000),
            'auxilio_transporte' => config('nomina.auxilio_transporte.valor_actual', 140606),
            'porcentajes' => [
                'salud_empleado' => config('nomina.porcentajes.salud_empleado', 4.0),
                'salud_empleador' => config('nomina.porcentajes.salud_empleador', 8.5),
                'pension_empleado' => config('nomina.porcentajes.pension_empleado', 4.0),
                'pension_empleador' => config('nomina.porcentajes.pension_empleador', 12.0),
                'sena' => config('nomina.porcentajes.sena', 2.0),
                'icbf' => config('nomina.porcentajes.icbf', 3.0),
                'caja' => config('nomina.porcentajes.caja', 4.0),
            ],
            'empresa' => [
                'razon_social' => config('nomina.empresa.razon_social', ''),
                'nit' => config('nomina.empresa.nit', ''),
                'direccion' => config('nomina.empresa.direccion', ''),
                'ciudad' => config('nomina.empresa.ciudad', ''),
                'telefono' => config('nomina.empresa.telefono', ''),
            ],
        ];
        
        return view('nomina.configuracion.index', compact('configuracion'));
    }

    /**
     * Actualizar configuración
     */
    public function actualizar(Request $request)
    {
        $request->validate([
            'smlv' => 'required|numeric|min:0',
            'auxilio_transporte' => 'required|numeric|min:0',
            'porcentajes.salud_empleado' => 'required|numeric|min:0|max:100',
            'porcentajes.salud_empleador' => 'required|numeric|min:0|max:100',
            'empresa.razon_social' => 'required|string|max:255',
            'empresa.nit' => 'required|string|max:50',
        ]);
        
        try {
            // Aquí guardarías en base de datos o archivo de configuración
            Cache::put('nomina.configuracion', $request->all(), now()->addYear());
            
            return back()->with('success', 'Configuración actualizada exitosamente');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar configuración: ' . $e->getMessage());
        }
    }

    /**
     * Importar configuración
     */
    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:json',
        ]);
        
        try {
            $contenido = file_get_contents($request->file('archivo')->getRealPath());
            $configuracion = json_decode($contenido, true);
            
            Cache::put('nomina.configuracion', $configuracion, now()->addYear());
            
            return back()->with('success', 'Configuración importada exitosamente');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error al importar configuración: ' . $e->getMessage());
        }
    }

    /**
     * Exportar configuración
     */
    public function exportar()
    {
        $configuracion = Cache::get('nomina.configuracion', []);
        
        $filename = 'configuracion-nomina-' . now()->format('Y-m-d') . '.json';
        
        return response()
            ->json($configuracion, 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
    }
}