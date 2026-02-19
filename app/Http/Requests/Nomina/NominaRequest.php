<?php

namespace App\Http\Requests\Nomina;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NominaRequest extends FormRequest
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
        $nominaId = $this->route('nomina');

        return [
            // Datos Básicos
            'numero_nomina' => [
                'required',
                'string',
                'max:50',
                Rule::unique('nominas', 'numero_nomina')->ignore($nominaId),
            ],
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string|max:500',
            
            // Tipo y Período
            'tipo_nomina_id' => 'required|exists:tipos_nomina,id',
            'periodo_nomina_id' => 'required|exists:periodos_nomina,id',
            
            // Fechas
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'fecha_pago' => 'required|date|after_or_equal:fecha_fin',
            'fecha_corte' => 'nullable|date',
            
            // Configuración
            'incluir_seguridad_social' => 'nullable|boolean',
            'incluir_parafiscales' => 'nullable|boolean',
            'incluir_provisiones' => 'nullable|boolean',
            'aplicar_retencion' => 'nullable|boolean',
            
            // Estado
            'estado' => 'required|in:borrador,preliquidada,aprobada,contabilizada,pagada,anulada',
            
            // Empleados
            'empleados' => 'nullable|array',
            'empleados.*' => 'exists:empleados,id',
            
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
            'numero_nomina.required' => 'El número de nómina es obligatorio',
            'numero_nomina.unique' => 'Ya existe una nómina con este número',
            'nombre.required' => 'El nombre de la nómina es obligatorio',
            'tipo_nomina_id.required' => 'El tipo de nómina es obligatorio',
            'tipo_nomina_id.exists' => 'El tipo de nómina seleccionado no existe',
            'periodo_nomina_id.required' => 'El período de nómina es obligatorio',
            'periodo_nomina_id.exists' => 'El período seleccionado no existe',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_fin.required' => 'La fecha de finalización es obligatoria',
            'fecha_fin.after_or_equal' => 'La fecha de finalización debe ser igual o posterior a la fecha de inicio',
            'fecha_pago.required' => 'La fecha de pago es obligatoria',
            'fecha_pago.after_or_equal' => 'La fecha de pago debe ser igual o posterior a la fecha de finalización',
            'estado.required' => 'El estado es obligatorio',
            'estado.in' => 'El estado seleccionado no es válido',
            'empleados.*.exists' => 'Uno o más empleados seleccionados no existen',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'numero_nomina' => 'número de nómina',
            'nombre' => 'nombre',
            'descripcion' => 'descripción',
            'tipo_nomina_id' => 'tipo de nómina',
            'periodo_nomina_id' => 'período',
            'fecha_inicio' => 'fecha de inicio',
            'fecha_fin' => 'fecha de finalización',
            'fecha_pago' => 'fecha de pago',
            'fecha_corte' => 'fecha de corte',
            'incluir_seguridad_social' => 'incluir seguridad social',
            'incluir_parafiscales' => 'incluir parafiscales',
            'incluir_provisiones' => 'incluir provisiones',
            'aplicar_retencion' => 'aplicar retención',
            'estado' => 'estado',
            'empleados' => 'empleados',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar que el período esté abierto
            if ($this->periodo_nomina_id) {
                $periodo = \App\Modules\Nomina\Models\PeriodoNomina::find($this->periodo_nomina_id);
                
                if ($periodo && $periodo->estado === 'cerrado') {
                    $validator->errors()->add('periodo_nomina_id', 'No se puede crear nómina en un período cerrado');
                }
            }

            // Validar que las fechas estén dentro del período
            if ($this->periodo_nomina_id && $this->fecha_inicio && $this->fecha_fin) {
                $periodo = \App\Modules\Nomina\Models\PeriodoNomina::find($this->periodo_nomina_id);
                
                if ($periodo) {
                    $inicioPermitido = $periodo->fecha_inicio;
                    $finPermitido = $periodo->fecha_fin;
                    
                    $inicio = \Carbon\Carbon::parse($this->fecha_inicio);
                    $fin = \Carbon\Carbon::parse($this->fecha_fin);
                    
                    if ($inicio->lt($inicioPermitido) || $inicio->gt($finPermitido)) {
                        $validator->errors()->add('fecha_inicio', 'La fecha de inicio debe estar dentro del período seleccionado');
                    }
                    
                    if ($fin->lt($inicioPermitido) || $fin->gt($finPermitido)) {
                        $validator->errors()->add('fecha_fin', 'La fecha de finalización debe estar dentro del período seleccionado');
                    }
                }
            }

            // Validar cambios de estado
            if ($this->route('nomina')) {
                $nominaActual = \App\Modules\Nomina\Models\Nomina::find($this->route('nomina'));
                
                if ($nominaActual) {
                    $estadoActual = $nominaActual->estado;
                    $estadoNuevo = $this->estado;
                    
                    // No se puede volver a borrador si ya está aprobada
                    if ($estadoActual === 'aprobada' && $estadoNuevo === 'borrador') {
                        $validator->errors()->add('estado', 'No se puede regresar a borrador una nómina aprobada');
                    }
                    
                    // No se puede modificar si está contabilizada
                    if ($estadoActual === 'contabilizada' && $estadoNuevo !== 'contabilizada' && $estadoNuevo !== 'anulada') {
                        $validator->errors()->add('estado', 'No se puede modificar una nómina contabilizada. Solo se puede anular.');
                    }
                    
                    // No se puede modificar si está pagada
                    if ($estadoActual === 'pagada' && $estadoNuevo !== 'pagada') {
                        $validator->errors()->add('estado', 'No se puede modificar una nómina pagada');
                    }
                }
            }

            // Validar que la fecha de pago no sea muy lejana
            if ($this->fecha_pago && $this->fecha_fin) {
                $fin = \Carbon\Carbon::parse($this->fecha_fin);
                $pago = \Carbon\Carbon::parse($this->fecha_pago);
                
                $diasDiferencia = $fin->diffInDays($pago);
                
                if ($diasDiferencia > 30) {
                    session()->flash('warning', 'La fecha de pago está a más de 30 días de la fecha de finalización. Verifique que sea correcto.');
                }
            }

            // Validar que haya empleados seleccionados (solo al crear)
            if (!$this->route('nomina') && empty($this->empleados)) {
                session()->flash('warning', 'No se han seleccionado empleados para esta nómina');
            }

            // Validar duplicados de nómina en el mismo período
            if ($this->tipo_nomina_id && $this->periodo_nomina_id) {
                $existe = \App\Modules\Nomina\Models\Nomina::where('tipo_nomina_id', $this->tipo_nomina_id)
                    ->where('periodo_nomina_id', $this->periodo_nomina_id)
                    ->where('estado', '!=', 'anulada')
                    ->when($this->route('nomina'), function($q) {
                        return $q->where('id', '!=', $this->route('nomina'));
                    })
                    ->exists();
                
                if ($existe) {
                    $validator->errors()->add('periodo_nomina_id', 'Ya existe una nómina de este tipo para el período seleccionado');
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Generar número de nómina si no existe
        if (empty($this->numero_nomina) && $this->periodo_nomina_id && $this->tipo_nomina_id) {
            $periodo = \App\Modules\Nomina\Models\PeriodoNomina::find($this->periodo_nomina_id);
            $tipo = \App\Modules\Nomina\Models\TipoNomina::find($this->tipo_nomina_id);
            
            if ($periodo && $tipo) {
                $consecutivo = \App\Modules\Nomina\Models\Nomina::where('periodo_nomina_id', $this->periodo_nomina_id)
                    ->where('tipo_nomina_id', $this->tipo_nomina_id)
                    ->count() + 1;
                
                $numeroNomina = sprintf(
                    'NOM-%s-%04d-%02d-%03d',
                    strtoupper(substr($tipo->nombre, 0, 3)),
                    $periodo->anio,
                    $periodo->mes,
                    $consecutivo
                );
                
                $this->merge(['numero_nomina' => $numeroNomina]);
            }
        }

        // Valores por defecto
        $this->merge([
            'incluir_seguridad_social' => $this->incluir_seguridad_social ?? true,
            'incluir_parafiscales' => $this->incluir_parafiscales ?? true,
            'incluir_provisiones' => $this->incluir_provisiones ?? true,
            'aplicar_retencion' => $this->aplicar_retencion ?? true,
            'estado' => $this->estado ?? 'borrador',
        ]);

        // Establecer fecha de corte si no existe
        if (empty($this->fecha_corte)) {
            $this->merge(['fecha_corte' => $this->fecha_fin ?? now()->format('Y-m-d')]);
        }
    }
}