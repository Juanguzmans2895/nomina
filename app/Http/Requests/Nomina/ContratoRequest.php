<?php

namespace App\Http\Requests\Nomina;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContratoRequest extends FormRequest
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
        $contratoId = $this->route('contrato');

        return [
            // Datos del Contrato
            'numero_contrato' => [
                'required',
                'string',
                'max:50',
                Rule::unique('contratos', 'numero_contrato')->ignore($contratoId),
            ],
            'tipo_contrato' => 'required|in:prestacion_servicios,obra_labor,suministro',
            'objeto' => 'required|string|max:1000',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'plazo_dias' => 'required|integer|min:1',
            
            // Datos del Contratista
            'tipo_documento_contratista' => 'required|in:CC,CE,NIT,PA',
            'numero_documento_contratista' => 'required|string|max:20',
            'nombre_contratista' => 'required|string|max:200',
            'direccion_contratista' => 'nullable|string|max:200',
            'telefono_contratista' => 'nullable|string|max:20',
            'email_contratista' => 'nullable|email|max:100',
            
            // Valores
            'valor_total' => 'required|numeric|min:0',
            'valor_mensual' => 'nullable|numeric|min:0',
            'anticipo' => 'nullable|numeric|min:0|max:100',
            'porcentaje_anticipo' => 'nullable|numeric|min:0|max:100',
            
            // Retenciones
            'aplica_retencion_fuente' => 'required|boolean',
            'porcentaje_retencion_fuente' => 'nullable|numeric|min:0|max:100',
            'aplica_estampilla' => 'nullable|boolean',
            'porcentaje_estampilla' => 'nullable|numeric|min:0|max:100',
            
            // Asignaciones
            'supervisor_id' => 'nullable|exists:empleados,id',
            'centro_costo_id' => 'nullable|exists:centros_costo,id',
            'rubro_presupuestal_id' => 'nullable|exists:rubros_presupuestales,id',
            
            // Estado
            'estado' => 'required|in:borrador,aprobado,en_ejecucion,suspendido,terminado,liquidado,anulado',
            'requiere_polizas' => 'nullable|boolean',
            'requiere_afiliacion_seguridad_social' => 'nullable|boolean',
            
            // Observaciones
            'observaciones' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'numero_contrato.required' => 'El número de contrato es obligatorio',
            'numero_contrato.unique' => 'Ya existe un contrato con este número',
            'tipo_contrato.required' => 'El tipo de contrato es obligatorio',
            'objeto.required' => 'El objeto del contrato es obligatorio',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_fin.required' => 'La fecha de finalización es obligatoria',
            'fecha_fin.after' => 'La fecha de finalización debe ser posterior a la fecha de inicio',
            'plazo_dias.required' => 'El plazo en días es obligatorio',
            'plazo_dias.min' => 'El plazo debe ser al menos 1 día',
            'numero_documento_contratista.required' => 'El documento del contratista es obligatorio',
            'nombre_contratista.required' => 'El nombre del contratista es obligatorio',
            'valor_total.required' => 'El valor total del contrato es obligatorio',
            'valor_total.min' => 'El valor total debe ser mayor a cero',
            'email_contratista.email' => 'El formato del email no es válido',
            'anticipo.max' => 'El anticipo no puede superar el valor total',
            'porcentaje_anticipo.max' => 'El porcentaje de anticipo no puede superar 100%',
            'porcentaje_retencion_fuente.max' => 'El porcentaje de retención no puede superar 100%',
            'supervisor_id.exists' => 'El supervisor seleccionado no existe',
            'centro_costo_id.exists' => 'El centro de costo seleccionado no existe',
            'rubro_presupuestal_id.exists' => 'El rubro presupuestal seleccionado no existe',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'numero_contrato' => 'número de contrato',
            'tipo_contrato' => 'tipo de contrato',
            'objeto' => 'objeto del contrato',
            'fecha_inicio' => 'fecha de inicio',
            'fecha_fin' => 'fecha de finalización',
            'plazo_dias' => 'plazo en días',
            'tipo_documento_contratista' => 'tipo de documento',
            'numero_documento_contratista' => 'número de documento',
            'nombre_contratista' => 'nombre del contratista',
            'direccion_contratista' => 'dirección',
            'telefono_contratista' => 'teléfono',
            'email_contratista' => 'correo electrónico',
            'valor_total' => 'valor total',
            'valor_mensual' => 'valor mensual',
            'anticipo' => 'anticipo',
            'porcentaje_anticipo' => 'porcentaje de anticipo',
            'aplica_retencion_fuente' => 'aplica retención',
            'porcentaje_retencion_fuente' => 'porcentaje de retención',
            'supervisor_id' => 'supervisor',
            'centro_costo_id' => 'centro de costo',
            'rubro_presupuestal_id' => 'rubro presupuestal',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Calcular plazo si no se proporciona
            if ($this->fecha_inicio && $this->fecha_fin) {
                $inicio = \Carbon\Carbon::parse($this->fecha_inicio);
                $fin = \Carbon\Carbon::parse($this->fecha_fin);
                $plazoCalculado = $inicio->diffInDays($fin) + 1;
                
                // Si el plazo proporcionado no coincide, actualizar
                if ($this->plazo_dias != $plazoCalculado) {
                    $this->merge(['plazo_dias' => $plazoCalculado]);
                }
            }

            // Validar que si aplica retención, tenga porcentaje
            if ($this->aplica_retencion_fuente && empty($this->porcentaje_retencion_fuente)) {
                $validator->errors()->add('porcentaje_retencion_fuente', 'Debe especificar el porcentaje de retención');
            }

            // Validar valor mensual vs valor total
            if ($this->valor_mensual && $this->plazo_dias) {
                $meses = ceil($this->plazo_dias / 30);
                $valorTotalEstimado = $this->valor_mensual * $meses;
                
                // Advertencia si hay mucha diferencia
                if (abs($valorTotalEstimado - $this->valor_total) > ($this->valor_total * 0.1)) {
                    // Diferencia mayor al 10%
                    session()->flash('warning', 'El valor mensual y el valor total no coinciden. Verifique los valores.');
                }
            }

            // Validar anticipo
            if ($this->anticipo && $this->anticipo > $this->valor_total) {
                $validator->errors()->add('anticipo', 'El anticipo no puede ser mayor al valor total del contrato');
            }

            // Calcular porcentaje de anticipo
            if ($this->anticipo && $this->valor_total > 0) {
                $porcentajeCalculado = ($this->anticipo / $this->valor_total) * 100;
                $this->merge(['porcentaje_anticipo' => round($porcentajeCalculado, 2)]);
            }

            // Validar estado vs fechas
            if ($this->estado === 'en_ejecucion') {
                $hoy = now();
                $inicio = \Carbon\Carbon::parse($this->fecha_inicio);
                $fin = \Carbon\Carbon::parse($this->fecha_fin);
                
                if ($hoy->lt($inicio)) {
                    $validator->errors()->add('estado', 'No se puede marcar como "En Ejecución" un contrato que aún no ha iniciado');
                }
                
                if ($hoy->gt($fin)) {
                    $validator->errors()->add('estado', 'No se puede marcar como "En Ejecución" un contrato que ya finalizó');
                }
            }

            // Validar documentos del contratista
            if ($this->tipo_documento_contratista === 'NIT') {
                // Para NIT, el nombre debería ser razón social
                if (strpos(strtolower($this->nombre_contratista), 's.a.s') === false && 
                    strpos(strtolower($this->nombre_contratista), 'ltda') === false) {
                    session()->flash('info', 'Verifique que el nombre sea la razón social completa de la empresa');
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Normalizar número de contrato
        if ($this->numero_contrato) {
            $this->merge([
                'numero_contrato' => strtoupper(trim($this->numero_contrato)),
            ]);
        }

        // Valores por defecto
        $this->merge([
            'aplica_retencion_fuente' => $this->aplica_retencion_fuente ?? true,
            'aplica_estampilla' => $this->aplica_estampilla ?? false,
            'requiere_polizas' => $this->requiere_polizas ?? false,
            'requiere_afiliacion_seguridad_social' => $this->requiere_afiliacion_seguridad_social ?? false,
        ]);
    }
}