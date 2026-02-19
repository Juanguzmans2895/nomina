<?php

namespace App\Http\Requests\Nomina;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmpleadoRequest extends FormRequest
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
        $empleadoId = $this->route('empleado');
        $smlv = config('nomina.smlv.valor_actual', 1300000);

        return [
            // Datos Personales
            'tipo_documento' => 'required|in:CC,CE,TI,PA,NIT',
            'numero_documento' => [
                'required',
                'string',
                'max:20',
                Rule::unique('empleados', 'numero_documento')->ignore($empleadoId),
            ],
            'primer_nombre' => 'required|string|max:50|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'segundo_nombre' => 'nullable|string|max:50|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'primer_apellido' => 'required|string|max:50|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'segundo_apellido' => 'nullable|string|max:50|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'fecha_nacimiento' => [
                'required',
                'date',
                'before:today',
                'after:' . now()->subYears(100)->format('Y-m-d'),
            ],
            'genero' => 'required|in:M,F,O',
            'estado_civil' => 'nullable|in:soltero,casado,union_libre,viudo,divorciado',
            'email' => [
                'nullable',
                'email:rfc,dns',
                'max:100',
                Rule::unique('empleados', 'email')->ignore($empleadoId),
            ],
            'telefono' => 'nullable|string|max:20|regex:/^[0-9\-\+\(\)\s]+$/',
            'celular' => 'nullable|string|max:20|regex:/^[0-9\-\+\(\)\s]+$/',
            'direccion' => 'nullable|string|max:200',
            'ciudad' => 'nullable|string|max:100',
            
            // Datos Laborales
            'codigo_empleado' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('empleados', 'codigo_empleado')->ignore($empleadoId),
            ],
            'fecha_ingreso' => [
                'required',
                'date',
                'before_or_equal:today',
                'after:fecha_nacimiento',
            ],
            'fecha_retiro' => 'nullable|date|after:fecha_ingreso',
            'tipo_contrato' => 'required|in:indefinido,fijo,obra_labor,prestacion_servicios',
            'cargo' => 'required|string|max:100',
            'dependencia' => 'nullable|string|max:100',
            'salario_basico' => [
                'required',
                'numeric',
                'min:' . $smlv,
                'max:999999999.99',
            ],
            'estado' => 'required|in:activo,inactivo,retirado,suspendido',
            
            // Datos Bancarios
            'banco' => 'nullable|string|max:100',
            'tipo_cuenta' => 'nullable|in:ahorros,corriente',
            'numero_cuenta' => 'nullable|string|max:50|regex:/^[0-9]+$/',
            
            // Seguridad Social
            'eps' => 'nullable|string|max:100',
            'eps_codigo' => 'nullable|string|max:10',
            'fondo_pension' => 'nullable|string|max:100',
            'pension_codigo' => 'nullable|string|max:10',
            'arl' => 'nullable|string|max:100',
            'arl_codigo' => 'nullable|string|max:10',
            'caja_compensacion' => 'nullable|string|max:100',
            'caja_codigo' => 'nullable|string|max:10',
            'clase_riesgo' => [
                'required',
                'numeric',
                'min:0.522',
                'max:6.96',
            ],
            
            // Otros
            'exento_retencion' => 'boolean',
            'calcula_auxilio_transporte' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Documentos
            'numero_documento.required' => 'El número de documento es obligatorio',
            'numero_documento.unique' => 'Ya existe un empleado con este número de documento',
            
            // Nombres
            'primer_nombre.required' => 'El primer nombre es obligatorio',
            'primer_nombre.regex' => 'El primer nombre solo puede contener letras',
            'primer_apellido.required' => 'El primer apellido es obligatorio',
            'primer_apellido.regex' => 'El primer apellido solo puede contener letras',
            
            // Fechas
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy',
            'fecha_nacimiento.after' => 'La fecha de nacimiento no es válida',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria',
            'fecha_ingreso.before_or_equal' => 'La fecha de ingreso no puede ser futura',
            'fecha_ingreso.after' => 'La fecha de ingreso debe ser posterior a la fecha de nacimiento',
            'fecha_retiro.after' => 'La fecha de retiro debe ser posterior a la fecha de ingreso',
            
            // Email
            'email.email' => 'El formato del email no es válido',
            'email.unique' => 'Ya existe un empleado con este email',
            
            // Teléfonos
            'telefono.regex' => 'El formato del teléfono no es válido',
            'celular.regex' => 'El formato del celular no es válido',
            
            // Laborales
            'codigo_empleado.unique' => 'Ya existe un empleado con este código',
            'cargo.required' => 'El cargo es obligatorio',
            'salario_basico.required' => 'El salario básico es obligatorio',
            'salario_basico.min' => 'El salario no puede ser menor al SMLV vigente ($' . number_format(config('nomina.smlv.valor_actual')) . ')',
            
            // Bancarios
            'numero_cuenta.regex' => 'El número de cuenta solo puede contener dígitos',
            
            // Seguridad Social
            'clase_riesgo.min' => 'La clase de riesgo mínima es 0.522',
            'clase_riesgo.max' => 'La clase de riesgo máxima es 6.96',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'tipo_documento' => 'tipo de documento',
            'numero_documento' => 'número de documento',
            'primer_nombre' => 'primer nombre',
            'segundo_nombre' => 'segundo nombre',
            'primer_apellido' => 'primer apellido',
            'segundo_apellido' => 'segundo apellido',
            'fecha_nacimiento' => 'fecha de nacimiento',
            'genero' => 'género',
            'estado_civil' => 'estado civil',
            'codigo_empleado' => 'código de empleado',
            'fecha_ingreso' => 'fecha de ingreso',
            'fecha_retiro' => 'fecha de retiro',
            'tipo_contrato' => 'tipo de contrato',
            'salario_basico' => 'salario básico',
            'tipo_cuenta' => 'tipo de cuenta',
            'numero_cuenta' => 'número de cuenta',
            'fondo_pension' => 'fondo de pensión',
            'pension_codigo' => 'código de pensión',
            'caja_compensacion' => 'caja de compensación',
            'caja_codigo' => 'código de caja',
            'clase_riesgo' => 'clase de riesgo',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que si tiene fecha de retiro, el estado sea 'retirado'
            if ($this->fecha_retiro && $this->estado !== 'retirado') {
                $validator->errors()->add('estado', 'Si hay fecha de retiro, el estado debe ser "retirado"');
            }

            // Validar que si es retirado, tenga fecha de retiro
            if ($this->estado === 'retirado' && !$this->fecha_retiro) {
                $validator->errors()->add('fecha_retiro', 'Los empleados retirados deben tener fecha de retiro');
            }

            // Validar que si tiene cuenta bancaria, tenga banco y tipo
            if ($this->numero_cuenta && (!$this->banco || !$this->tipo_cuenta)) {
                $validator->errors()->add('banco', 'Si tiene número de cuenta, debe especificar banco y tipo de cuenta');
            }

            // Validar edad mínima (18 años al ingresar)
            if ($this->fecha_nacimiento && $this->fecha_ingreso) {
                $fechaNacimiento = \Carbon\Carbon::parse($this->fecha_nacimiento);
                $fechaIngreso = \Carbon\Carbon::parse($this->fecha_ingreso);
                
                $edadAlIngresar = $fechaNacimiento->diffInYears($fechaIngreso);
                
                if ($edadAlIngresar < 18) {
                    $validator->errors()->add('fecha_ingreso', 'El empleado debe tener al menos 18 años al momento de ingresar');
                }
            }
        });
    }
}