<?php

namespace App\Http\Requests\Nomina;

use Illuminate\Foundation\Http\FormRequest;

class NovedadNominaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'empleado_id' => 'required|exists:empleados,id',
            'concepto_nomina_id' => 'required|exists:conceptos_nomina,id',
            'periodo_nomina_id' => 'required|exists:periodos_nomina,id',
            'fecha_novedad' => 'required|date',
            'cantidad' => 'required|numeric|min:0',
            'valor_unitario' => 'required|numeric|min:0',
            'valor_total' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
            'requiere_aprobacion' => 'nullable|boolean',
            'estado' => 'nullable|in:pendiente,aprobada,rechazada,procesada',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'empleado_id.required' => 'El empleado es obligatorio',
            'empleado_id.exists' => 'El empleado seleccionado no existe',
            'concepto_nomina_id.required' => 'El concepto es obligatorio',
            'concepto_nomina_id.exists' => 'El concepto seleccionado no existe',
            'periodo_nomina_id.required' => 'El período es obligatorio',
            'periodo_nomina_id.exists' => 'El período seleccionado no existe',
            'fecha_novedad.required' => 'La fecha de la novedad es obligatoria',
            'cantidad.required' => 'La cantidad es obligatoria',
            'cantidad.min' => 'La cantidad debe ser mayor o igual a cero',
            'valor_unitario.required' => 'El valor unitario es obligatorio',
            'valor_unitario.min' => 'El valor unitario debe ser mayor o igual a cero',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Calcular valor total
            if ($this->cantidad !== null && $this->valor_unitario !== null) {
                $valorTotal = $this->cantidad * $this->valor_unitario;
                $this->merge(['valor_total' => $valorTotal]);
            }

            // Validar que el concepto esté activo
            if ($this->concepto_nomina_id) {
                $concepto = \App\Modules\Nomina\Models\ConceptoNomina::find($this->concepto_nomina_id);
                
                if ($concepto && !$concepto->activo) {
                    $validator->errors()->add('concepto_nomina_id', 'El concepto seleccionado está inactivo');
                }

                // Validar que el concepto sea de tipo novedad
                if ($concepto && $concepto->tipo !== 'novedad') {
                    $validator->errors()->add('concepto_nomina_id', 'Solo se pueden crear novedades con conceptos de tipo "novedad"');
                }
            }

            // Validar que el empleado esté activo
            if ($this->empleado_id) {
                $empleado = \App\Modules\Nomina\Models\Empleado::find($this->empleado_id);
                
                if ($empleado && $empleado->estado !== 'activo') {
                    $validator->errors()->add('empleado_id', 'Solo se pueden crear novedades para empleados activos');
                }
            }

            // Validar que el período esté abierto
            if ($this->periodo_nomina_id) {
                $periodo = \App\Modules\Nomina\Models\PeriodoNomina::find($this->periodo_nomina_id);
                
                if ($periodo && $periodo->estado === 'cerrado') {
                    $validator->errors()->add('periodo_nomina_id', 'No se pueden crear novedades en un período cerrado');
                }
            }

            // Validar que la fecha esté dentro del período
            if ($this->fecha_novedad && $this->periodo_nomina_id) {
                $periodo = \App\Modules\Nomina\Models\PeriodoNomina::find($this->periodo_nomina_id);
                
                if ($periodo) {
                    $fechaNovedad = \Carbon\Carbon::parse($this->fecha_novedad);
                    
                    if ($fechaNovedad->lt($periodo->fecha_inicio) || $fechaNovedad->gt($periodo->fecha_fin)) {
                        $validator->errors()->add('fecha_novedad', 'La fecha de la novedad debe estar dentro del período seleccionado');
                    }
                }
            }

            // Validar duplicados
            if ($this->empleado_id && $this->concepto_nomina_id && $this->fecha_novedad && !$this->route('novedad')) {
                $existe = \App\Modules\Nomina\Models\NovedadNomina::where('empleado_id', $this->empleado_id)
                    ->where('concepto_nomina_id', $this->concepto_nomina_id)
                    ->where('fecha_novedad', $this->fecha_novedad)
                    ->where('estado', '!=', 'rechazada')
                    ->exists();
                
                if ($existe) {
                    $validator->errors()->add('concepto_nomina_id', 'Ya existe una novedad para este empleado, concepto y fecha');
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'estado' => $this->estado ?? 'pendiente',
            'requiere_aprobacion' => $this->requiere_aprobacion ?? false,
            'procesada' => false,
        ]);
    }
}