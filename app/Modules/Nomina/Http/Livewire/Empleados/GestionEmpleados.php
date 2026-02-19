<?php

namespace App\Modules\Nomina\Http\Livewire\Empleados;

use Livewire\Component;
use Livewire\WithPagination;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\CentroCosto;
use App\Modules\Nomina\Models\ConceptoNomina;
use Illuminate\Validation\Rule;

class GestionEmpleados extends Component
{
    use WithPagination;

    // Propiedades del formulario
    public $empleadoId;
    public $showModal = false;
    public $showDeleteModal = false;
    public $modalTitle = 'Nuevo Empleado';
    
    // Datos Personales
    public $tipo_documento = 'CC';
    public $numero_documento;
    public $primer_nombre;
    public $segundo_nombre;
    public $primer_apellido;
    public $segundo_apellido;
    public $fecha_nacimiento;
    public $genero;
    public $estado_civil;
    public $email;
    public $telefono;
    public $celular;
    public $direccion;
    public $ciudad;
    
    // Datos Laborales
    public $codigo_empleado;
    public $fecha_ingreso;
    public $tipo_contrato = 'indefinido';
    public $cargo;
    public $dependencia;
    public $salario_basico;
    public $estado = 'activo';
    
    // Datos Bancarios
    public $banco;
    public $tipo_cuenta;
    public $numero_cuenta;
    
    // Seguridad Social
    public $eps;
    public $eps_codigo;
    public $fondo_pension;
    public $pension_codigo;
    public $arl;
    public $arl_codigo;
    public $caja_compensacion;
    public $caja_codigo;
    public $clase_riesgo = 0.522;
    
    // Búsqueda y filtros
    public $search = '';
    public $filterEstado = '';
    public $filterTipoContrato = '';
    
    protected $queryString = ['search', 'filterEstado', 'filterTipoContrato'];

    protected function rules()
    {
        return [
            // Datos Personales
            'tipo_documento' => 'required|in:CC,CE,TI,PA,NIT',
            'numero_documento' => [
                'required',
                'string',
                'max:20',
                Rule::unique('empleados', 'numero_documento')->ignore($this->empleadoId)
            ],
            'primer_nombre' => 'required|string|max:50',
            'segundo_nombre' => 'nullable|string|max:50',
            'primer_apellido' => 'required|string|max:50',
            'segundo_apellido' => 'nullable|string|max:50',
            'fecha_nacimiento' => 'required|date|before:today',
            'genero' => 'required|in:M,F,O',
            'estado_civil' => 'nullable|in:soltero,casado,union_libre,viudo,divorciado',
            'email' => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('empleados', 'email')->ignore($this->empleadoId)
            ],
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:200',
            'ciudad' => 'nullable|string|max:100',
            
            // Datos Laborales
            'codigo_empleado' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('empleados', 'codigo_empleado')->ignore($this->empleadoId)
            ],
            'fecha_ingreso' => 'required|date',
            'tipo_contrato' => 'required|in:indefinido,fijo,obra_labor,prestacion_servicios',
            'cargo' => 'required|string|max:100',
            'dependencia' => 'nullable|string|max:100',
            'salario_basico' => 'required|numeric|min:0',
            'estado' => 'required|in:activo,inactivo,retirado,suspendido',
            
            // Datos Bancarios
            'banco' => 'nullable|string|max:100',
            'tipo_cuenta' => 'nullable|in:ahorros,corriente',
            'numero_cuenta' => 'nullable|string|max:50',
            
            // Seguridad Social
            'eps' => 'nullable|string|max:100',
            'eps_codigo' => 'nullable|string|max:10',
            'fondo_pension' => 'nullable|string|max:100',
            'pension_codigo' => 'nullable|string|max:10',
            'arl' => 'nullable|string|max:100',
            'arl_codigo' => 'nullable|string|max:10',
            'caja_compensacion' => 'nullable|string|max:100',
            'caja_codigo' => 'nullable|string|max:10',
            'clase_riesgo' => 'required|numeric|min:0|max:10',
        ];
    }

    protected $messages = [
        'numero_documento.required' => 'El número de documento es obligatorio',
        'numero_documento.unique' => 'Ya existe un empleado con este número de documento',
        'primer_nombre.required' => 'El primer nombre es obligatorio',
        'primer_apellido.required' => 'El primer apellido es obligatorio',
        'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria',
        'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy',
        'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria',
        'cargo.required' => 'El cargo es obligatorio',
        'salario_basico.required' => 'El salario básico es obligatorio',
        'salario_basico.min' => 'El salario básico debe ser mayor a cero',
    ];

    public function render()
    {
        $empleados = Empleado::query()
            ->when($this->search, function($query) {
                $query->buscar($this->search);
            })
            ->when($this->filterEstado, function($query) {
                $query->where('estado', $this->filterEstado);
            })
            ->when($this->filterTipoContrato, function($query) {
                $query->porTipoContrato($this->filterTipoContrato);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('nomina.livewire.empleados.gestion-empleados', [
            'empleados' => $empleados
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->modalTitle = 'Nuevo Empleado';
        $this->showModal = true;
    }

    public function edit($id)
    {
        $empleado = Empleado::findOrFail($id);
        
        $this->empleadoId = $empleado->id;
        $this->modalTitle = 'Editar Empleado';
        
        // Datos Personales
        $this->tipo_documento = $empleado->tipo_documento;
        $this->numero_documento = $empleado->numero_documento;
        $this->primer_nombre = $empleado->primer_nombre;
        $this->segundo_nombre = $empleado->segundo_nombre;
        $this->primer_apellido = $empleado->primer_apellido;
        $this->segundo_apellido = $empleado->segundo_apellido;
        $this->fecha_nacimiento = $empleado->fecha_nacimiento?->format('Y-m-d');
        $this->genero = $empleado->genero;
        $this->estado_civil = $empleado->estado_civil;
        $this->email = $empleado->email;
        $this->telefono = $empleado->telefono;
        $this->celular = $empleado->celular;
        $this->direccion = $empleado->direccion;
        $this->ciudad = $empleado->ciudad;
        
        // Datos Laborales
        $this->codigo_empleado = $empleado->codigo_empleado;
        $this->fecha_ingreso = $empleado->fecha_ingreso?->format('Y-m-d');
        $this->tipo_contrato = $empleado->tipo_contrato;
        $this->cargo = $empleado->cargo;
        $this->dependencia = $empleado->dependencia;
        $this->salario_basico = $empleado->salario_basico;
        $this->estado = $empleado->estado;
        
        // Datos Bancarios
        $this->banco = $empleado->banco;
        $this->tipo_cuenta = $empleado->tipo_cuenta;
        $this->numero_cuenta = $empleado->numero_cuenta;
        
        // Seguridad Social
        $this->eps = $empleado->eps;
        $this->eps_codigo = $empleado->eps_codigo;
        $this->fondo_pension = $empleado->fondo_pension;
        $this->pension_codigo = $empleado->pension_codigo;
        $this->arl = $empleado->arl;
        $this->arl_codigo = $empleado->arl_codigo;
        $this->caja_compensacion = $empleado->caja_compensacion;
        $this->caja_codigo = $empleado->caja_codigo;
        $this->clase_riesgo = $empleado->clase_riesgo;
        
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                // Datos Personales
                'tipo_documento' => $this->tipo_documento,
                'numero_documento' => $this->numero_documento,
                'primer_nombre' => $this->primer_nombre,
                'segundo_nombre' => $this->segundo_nombre,
                'primer_apellido' => $this->primer_apellido,
                'segundo_apellido' => $this->segundo_apellido,
                'fecha_nacimiento' => $this->fecha_nacimiento,
                'genero' => $this->genero,
                'estado_civil' => $this->estado_civil,
                'email' => $this->email,
                'telefono' => $this->telefono,
                'celular' => $this->celular,
                'direccion' => $this->direccion,
                'ciudad' => $this->ciudad,
                
                // Datos Laborales
                'codigo_empleado' => $this->codigo_empleado,
                'fecha_ingreso' => $this->fecha_ingreso,
                'tipo_contrato' => $this->tipo_contrato,
                'cargo' => $this->cargo,
                'dependencia' => $this->dependencia,
                'salario_basico' => $this->salario_basico,
                'estado' => $this->estado,
                
                // Datos Bancarios
                'banco' => $this->banco,
                'tipo_cuenta' => $this->tipo_cuenta,
                'numero_cuenta' => $this->numero_cuenta,
                
                // Seguridad Social
                'eps' => $this->eps,
                'eps_codigo' => $this->eps_codigo,
                'fondo_pension' => $this->fondo_pension,
                'pension_codigo' => $this->pension_codigo,
                'arl' => $this->arl,
                'arl_codigo' => $this->arl_codigo,
                'caja_compensacion' => $this->caja_compensacion,
                'caja_codigo' => $this->caja_codigo,
                'clase_riesgo' => $this->clase_riesgo,
                
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ];

            if ($this->empleadoId) {
                $empleado = Empleado::findOrFail($this->empleadoId);
                $empleado->update($data);
                $message = 'Empleado actualizado exitosamente';
            } else {
                Empleado::create($data);
                $message = 'Empleado creado exitosamente';
            }

            $this->showModal = false;
            $this->resetForm();
            session()->flash('success', $message);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->empleadoId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        try {
            $empleado = Empleado::findOrFail($this->empleadoId);
            $empleado->delete();
            
            $this->showDeleteModal = false;
            session()->flash('success', 'Empleado eliminado exitosamente');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->empleadoId = null;
        $this->tipo_documento = 'CC';
        $this->numero_documento = '';
        $this->primer_nombre = '';
        $this->segundo_nombre = '';
        $this->primer_apellido = '';
        $this->segundo_apellido = '';
        $this->fecha_nacimiento = '';
        $this->genero = '';
        $this->estado_civil = '';
        $this->email = '';
        $this->telefono = '';
        $this->celular = '';
        $this->direccion = '';
        $this->ciudad = '';
        $this->codigo_empleado = '';
        $this->fecha_ingreso = '';
        $this->tipo_contrato = 'indefinido';
        $this->cargo = '';
        $this->dependencia = '';
        $this->salario_basico = '';
        $this->estado = 'activo';
        $this->banco = '';
        $this->tipo_cuenta = '';
        $this->numero_cuenta = '';
        $this->eps = '';
        $this->eps_codigo = '';
        $this->fondo_pension = '';
        $this->pension_codigo = '';
        $this->arl = '';
        $this->arl_codigo = '';
        $this->caja_compensacion = '';
        $this->caja_codigo = '';
        $this->clase_riesgo = 0.522;
        
        $this->resetValidation();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterEstado()
    {
        $this->resetPage();
    }

    public function updatingFilterTipoContrato()
    {
        $this->resetPage();
    }
}