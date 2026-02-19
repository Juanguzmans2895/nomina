<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Modules\Nomina\Models\PeriodoNomina;
use Illuminate\Http\Request;

class PeriodoController extends Controller
{
    /**
     * API: Períodos abiertos
     */
    public function abiertos()
    {
        $periodos = PeriodoNomina::where('estado', 'abierto')
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
        $valido = $periodo->estado === 'abierto';
        
        $mensaje = $valido 
            ? 'Período válido para liquidación'
            : 'El período está cerrado';
        
        return response()->json([
            'valido' => $valido,
            'mensaje' => $mensaje,
            'periodo' => $periodo,
        ]);
    }
}