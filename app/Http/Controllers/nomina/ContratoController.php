<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Nomina\Models\Contrato;
use App\Modules\Nomina\Models\Empleado;
use App\Http\Requests\Nomina\ContratoRequest;

class ContratoController extends Controller
{
    /**
     * Listado de contratos
     */
    public function index(Request $request)
    {
        $query = Contrato::query();
        
        // Filtro de búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero_contrato', 'like', "%{$search}%")
                  ->orWhere('nombre_contratista', 'like', "%{$search}%")
                  ->orWhere('numero_documento_contratista', 'like', "%{$search}%");
            });
        }
        
        // Filtro de estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        
        // Filtro de fecha
        if ($request->filled('fecha_inicio')) {
            $query->where('fecha_inicio', '>=', $request->fecha_inicio);
        }
        
        $contratos = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('nomina.livewire.contratos.gestion-contratos', compact('contratos'));
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        $supervisores = Empleado::where('estado', 'activo')
            ->orderBy('primer_apellido')
            ->get();
        
        return view('nomina.livewire.contratos.create', compact('supervisores'));
    }

    /**
     * Guardar nuevo contrato
     */
    public function store(ContratoRequest $request)
    {
        $data = $request->validated();
        
        // Calcular saldo pendiente inicial
        $data['saldo_pendiente'] = $data['valor_total'];
        $data['valor_pagado'] = 0;
        
        // Usuario que crea
        $data['created_by'] = auth()->id();
        
        $contrato = Contrato::create($data);
        
        return redirect()->route('nomina.contratos.index')
            ->with('success', 'Contrato creado exitosamente');
    }

    /**
     * Ver pagos del contrato
     */
    public function pagos(Contrato $contrato)
    {
        $contrato->load([
            'pagos' => function($query) {
                $query->orderBy('fecha_pago', 'desc');
            }
        ]);
        
        return view('nomina.livewire.contratos.registro-pago-contrato', compact('contrato'));
    }

    /**
    * Formulario para registrar pago
    */
    public function createPago(Contrato $contrato)
    {
        return view('nomina.livewire.contratos.contratos-pagos-create', compact('contrato'));
    }

    /**
     * Guardar pago
     */
    public function storePago(Request $request, Contrato $contrato)
    {
        $validated = $request->validate([
            'numero_pago' => 'required|string|max:50',
            'fecha_pago' => 'required|date',
            'valor_bruto' => 'required|numeric|min:0',
        ]);
        
        // Calcular valores
        $calculos = $contrato->calcularValorNeto($validated['valor_bruto']);
        
        $contrato->pagos()->create([
            'numero_pago' => $validated['numero_pago'],
            'fecha_pago' => $validated['fecha_pago'],
            'valor_bruto' => $calculos['valor_bruto'],
            'retencion_fuente' => $calculos['retencion_fuente'],
            'valor_neto' => $calculos['valor_neto'],
            'aprobado' => false,
            'pagado' => false,
            'created_by' => auth()->id(),
        ]);
        
        return redirect()->route('nomina.contratos.pagos', $contrato)
            ->with('success', 'Pago registrado exitosamente');
    }

    /**
     * Aprobar pago
     */
    public function aprobarPago(Contrato $contrato, $pagoId)
    {
        $pago = $contrato->pagos()->findOrFail($pagoId);
        
        $pago->update([
            'aprobado' => true,
            'aprobado_by' => auth()->id(),
            'fecha_aprobacion' => now(),
        ]);
        
        return redirect()->back()->with('success', 'Pago aprobado exitosamente');
    }

    /**
     * Marcar como pagado
     */
    public function marcarPagado(Contrato $contrato, $pagoId)
    {
        $pago = $contrato->pagos()->findOrFail($pagoId);
        
        $pago->update([
            'pagado' => true,
            'fecha_pago_real' => now(),
        ]);
        
        // Actualizar saldo del contrato
        $contrato->actualizarSaldo();
        
        return redirect()->back()->with('success', 'Pago marcado como realizado');
    }
    
    /**
     * Ver detalle del contrato
     */
    public function show(Contrato $contrato)
    {
        $contrato->load(['empleado', 'pagos']);
        return view('nomina.contratos.show', compact('contrato'));
    }

    /**
     * Formulario de editar contrato
     */
    public function edit(Contrato $contrato)
    {
        $empleados = Empleado::where('estado', 'activo')
            ->orderBy('primer_apellido')
            ->orderBy('primer_nombre')
            ->get();
        
        return view('nomina.contratos.edit', compact('contrato', 'empleados'));
    }

    /**
     * Actualizar contrato
     */
    public function update(Request $request, Contrato $contrato)
    {
        $validated = $request->validate([
            'tipo_contrato' => 'required|in:indefinido,fijo,obra_labor,prestacion_servicios',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'salario' => 'required|numeric|min:0',
            'cargo' => 'required|string|max:255',
            'dependencia' => 'nullable|string|max:255',
            'jornada_laboral' => 'nullable|string|max:255',
            'lugar_trabajo' => 'nullable|string|max:500',
            'clausulas' => 'nullable|string',
            'estado' => 'required|in:activo,finalizado,suspendido',
        ]);

        $contrato->update($validated);

        return redirect()->route('nomina.contratos.show', $contrato)
            ->with('success', 'Contrato actualizado exitosamente');
    }

    /**
     * Eliminar contrato
     */
    public function destroy(Contrato $contrato)
    {
        $contrato->delete();

        return redirect()->route('nomina.contratos.index')
            ->with('success', 'Contrato eliminado exitosamente');
    }

    /**
     * Editar pago del contrato
     */
    public function editPago(Contrato $contrato, $pagoId)
    {
        $pago = $contrato->pagos()->findOrFail($pagoId);
        return view('nomina.contratos.pagos.edit', compact('contrato', 'pago'));
    }

    /**
     * Actualizar pago del contrato
     */
    public function updatePago(Request $request, Contrato $contrato, $pagoId)
    {
        $pago = $contrato->pagos()->findOrFail($pagoId);
        
        $validated = $request->validate([
            'numero_pago' => 'required|string|max:50',
            'fecha_pago' => 'required|date',
            'valor_bruto' => 'required|numeric|min:0',
        ]);
        
        // Calcular valores
        $calculos = $contrato->calcularValorNeto($validated['valor_bruto']);
        
        $pago->update([
            'numero_pago' => $validated['numero_pago'],
            'fecha_pago' => $validated['fecha_pago'],
            'valor_bruto' => $calculos['valor_bruto'],
            'retencion_fuente' => $calculos['retencion_fuente'],
            'valor_neto' => $calculos['valor_neto'],
        ]);
        
        // Actualizar saldo del contrato
        $contrato->actualizarSaldo();
        
        return redirect()->route('nomina.contratos.pagos', $contrato)
            ->with('success', 'Pago actualizado exitosamente');
    }

    /**
     * Eliminar pago del contrato
     */
    public function destroyPago(Contrato $contrato, $pagoId)
    {
        $pago = $contrato->pagos()->findOrFail($pagoId);
        $pago->delete();
        
        // Actualizar saldo del contrato
        $contrato->actualizarSaldo();
        
        return redirect()->route('nomina.contratos.pagos', $contrato)
            ->with('success', 'Pago eliminado exitosamente');
    }
}