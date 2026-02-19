<?php

namespace App\Http\Requests\Nomina;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConceptoNominaRequest extends FormRequest
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
        $conceptoId = $this->route('concepto');

        return [
            'codigo' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9]+$/',
                Rule::unique('conceptos_nomina', 'codigo')->ignore($conceptoId),
            ],
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string|max:500',
            'clasificacion' => 'required|in:devengado,deducido,no_imputable',
            'tipo' => 'required|in:fijo,novedad,calculado',
            'prioridad' => 'required|integer|min:1|max:999',
            
            // Afectaciones
            'afecta_base_seguridad_social' => 'boolean',
            'afecta_base_parafiscales' => 'boolean',
            'afecta_base_retencion' => 'boolean',
            'afecta_base_provision_cesantias' => 'boolean',
            'afecta_base_provision_vacaciones' => 'boolean',
            
            // Contabilización
            'cuenta_debito_id' => [
                'nullable',
                'exists:cuentas_contables,id',
                Rule::requiredIf(function () {
                    return $this->clasificacion === 'devengado';
                }),
            ],
            'cuenta_credito_id' => [
                'nullable',
                'exists:cuentas_contables,id',
                Rule::requiredIf(function () {
                    return $this->clasificacion === 'deducido';
                }),
            ],
            
            // Configuración
            'porcentaje_empleado' => 'nullable|numeric|min:0|max:100',
            'porcentaje_empleador' => 'nullable|numeric|min:0|max:100',
            'valor_fijo' => 'nullable|numeric|min:0',
            'formula' => 'nullable|string|max:1000',
            
            // Estado
            'activo' => 'boolean',
            'visible_desprendible' => 'boolean',
            'requiere_aprobacion' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'codigo.required' => 'El código es obligatorio',
            'codigo.regex' => 'El código solo puede contener letras mayúsculas y números',
            'codigo.unique' => 'Ya existe un concepto con este código',
            'nombre.required' => 'El nombre es obligatorio',
            'clasificacion.required' => 'La clasificación es obligatoria',
            'clasificacion.in' => 'La clasificación debe ser: devengado, deducido o no imputable',
            'tipo.required' => 'El tipo es obligatorio',
            'tipo.in' => 'El tipo debe ser: fijo, novedad o calculado',
            'prioridad.required' => 'La prioridad es obligatoria',
            'prioridad.min' => 'La prioridad mínima es 1',
            'prioridad.max' => 'La prioridad máxima es 999',
            'cuenta_debito_id.required' => 'Los conceptos devengados requieren cuenta débito',
            'cuenta_credito_id.required' => 'Los conceptos deducidos requieren cuenta crédito',
            'porcentaje_empleado.min' => 'El porcentaje empleado no puede ser negativo',
            'porcentaje_empleado.max' => 'El porcentaje empleado no puede superar 100%',
            'porcentaje_empleador.min' => 'El porcentaje empleador no puede ser negativo',
            'porcentaje_empleador.max' => 'El porcentaje empleador no puede superar 100%',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'codigo' => 'código',
            'clasificacion' => 'clasificación',
            'cuenta_debito_id' => 'cuenta débito',
            'cuenta_credito_id' => 'cuenta crédito',
            'porcentaje_empleado' => 'porcentaje empleado',
            'porcentaje_empleador' => 'porcentaje empleador',
            'valor_fijo' => 'valor fijo',
            'visible_desprendible' => 'visible en desprendible',
            'requiere_aprobacion' => 'requiere aprobación',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Si es devengado, debe tener cuenta débito
            if ($this->clasificacion === 'devengado' && !$this->cuenta_debito_id) {
                $validator->errors()->add('cuenta_debito_id', 'Los conceptos devengados requieren una cuenta débito');
            }

            // Si es deducido, debe tener cuenta crédito
            if ($this->clasificacion === 'deducido' && !$this->cuenta_credito_id) {
                $validator->errors()->add('cuenta_credito_id', 'Los conceptos deducidos requieren una cuenta crédito');
            }

            // Validar que no se marquen todas las afectaciones como false en conceptos importantes
            if ($this->clasificacion !== 'no_imputable') {
                $tieneAfectacion = $this->afecta_base_seguridad_social ||
                                  $this->afecta_base_parafiscales ||
                                  $this->afecta_base_retencion ||
                                  $this->afecta_base_provision_cesantias ||
                                  $this->afecta_base_provision_vacaciones;

                if (!$tieneAfectacion) {
                    $validator->warnings()->add('afectaciones', 'Este concepto no afecta ninguna base de cálculo. Verifique que sea correcto.');
                }
            }

            // Si es tipo calculado, debe tener fórmula
            if ($this->tipo === 'calculado' && empty($this->formula)) {
                $validator->errors()->add('formula', 'Los conceptos calculados deben tener una fórmula');
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir código a mayúsculas
        if ($this->codigo) {
            $this->merge([
                'codigo' => strtoupper($this->codigo),
            ]);
        }

        // Valores por defecto
        $this->merge([
            'afecta_base_seguridad_social' => $this->afecta_base_seguridad_social ?? false,
            'afecta_base_parafiscales' => $this->afecta_base_parafiscales ?? false,
            'afecta_base_retencion' => $this->afecta_base_retencion ?? false,
            'afecta_base_provision_cesantias' => $this->afecta_base_provision_cesantias ?? false,
            'afecta_base_provision_vacaciones' => $this->afecta_base_provision_vacaciones ?? false,
            'activo' => $this->activo ?? true,
            'visible_desprendible' => $this->visible_desprendible ?? true,
            'requiere_aprobacion' => $this->requiere_aprobacion ?? false,
        ]);
    }
}