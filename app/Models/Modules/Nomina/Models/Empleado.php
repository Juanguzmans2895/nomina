<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\User;

class Empleado extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'empleados';

    protected $fillable = [
        // Información Personal
        'tipo_documento',
        'numero_documento',
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'fecha_nacimiento',
        'sexo',
        'estado_civil',
        
        // Contacto
        'email',
        'email_personal',
        'telefono_movil',
        'telefono_fijo',
        'direccion',
        'ciudad',
        'departamento',
        
        // Laboral
        'codigo_empleado',
        'fecha_ingreso',
        'fecha_retiro',
        'tipo_contrato',
        'cargo',
        'dependencia',
        'salario_basico',
        'estado',
        
        // Bancaria
        'banco',
        'tipo_cuenta',
        'numero_cuenta',
        
        // Seguridad Social
        'eps',
        'eps_codigo',
        'fondo_pension',
        'pension_codigo',
        'arl',
        'arl_codigo',
        'clase_riesgo',
        'caja_compensacion',
        'caja_codigo',
        
        // Adicional
        'numero_hijos',
        'nivel_educativo',
        'profesion',
        'observaciones',
        
        // Control
        'aplica_auxilio_transporte',
        'alto_riesgo_pension',
        'exento_retencion',
        'porcentaje_retencion',
        
        // Auditoría
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso' => 'date',
        'fecha_retiro' => 'date',
        'salario_basico' => 'decimal:2',
        'clase_riesgo' => 'decimal:3',
        'numero_hijos' => 'integer',
        'aplica_auxilio_transporte' => 'boolean',
        'alto_riesgo_pension' => 'boolean',
        'exento_retencion' => 'boolean',
        'porcentaje_retencion' => 'decimal:2',
    ];

    /**
     * Configuración de Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relación con centros de costo
     */
    public function centrosCosto(): BelongsToMany
    {
        return $this->belongsToMany(
            CentroCosto::class,
            'empleado_centro_costo',
            'empleado_id',
            'centro_costo_id'
        )
        ->withPivot('porcentaje', 'fecha_inicio', 'fecha_fin', 'activo')
        ->withTimestamps();
    }

    /**
     * Centros de costo activos
     */
    public function centrosCostoActivos(): BelongsToMany
    {
        return $this->centrosCosto()->wherePivot('activo', true);
    }

    /**
     * Relación con conceptos fijos
     */
    public function conceptosFijos(): BelongsToMany
    {
        return $this->belongsToMany(
            ConceptoNomina::class,
            'empleado_concepto_fijo',
            'empleado_id',
            'concepto_nomina_id'
        )
        ->withPivot('valor', 'porcentaje', 'cantidad', 'fecha_inicio', 'fecha_fin', 'activo', 'observaciones')
        ->withTimestamps();
    }

    /**
     * Conceptos fijos activos
     */
    public function conceptosFijosActivos(): BelongsToMany
    {
        return $this->conceptosFijos()->wherePivot('activo', true);
    }

    /**
     * Usuario que creó el registro
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario que actualizó el registro
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Accessor: Nombre completo
     */
    public function getNombreCompletoAttribute(): string
    {
        $nombres = trim($this->primer_nombre . ' ' . $this->segundo_nombre);
        $apellidos = trim($this->primer_apellido . ' ' . $this->segundo_apellido);
        return trim($nombres . ' ' . $apellidos);
    }

    /**
     * Accessor: Nombres
     */
    public function getNombresAttribute(): string
    {
        return trim($this->primer_nombre . ' ' . $this->segundo_nombre);
    }

    /**
     * Accessor: Apellidos
     */
    public function getApellidosAttribute(): string
    {
        return trim($this->primer_apellido . ' ' . $this->segundo_apellido);
    }

    /**
     * Accessor: Edad
     */
    public function getEdadAttribute(): int
    {
        return $this->fecha_nacimiento->age;
    }

    /**
     * Accessor: Años de servicio
     */
    public function getAniosServicioAttribute(): float
    {
        $fechaFin = $this->fecha_retiro ?? now();
        return $this->fecha_ingreso->diffInYears($fechaFin);
    }

    /**
     * Verificar si el empleado está activo
     */
    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }

    /**
     * Verificar si aplica seguridad social
     */
    public function aplicaSeguridadSocial(): bool
    {
        return in_array($this->tipo_contrato, ['indefinido', 'fijo', 'obra_labor']);
    }

    /**
     * Calcular auxilio de transporte
     */
    public function calculaAuxilioTransporte(): bool
    {
        $smlv = config('nomina.smlv.valor_actual');
        return $this->aplica_auxilio_transporte && ($this->salario_basico <= $smlv * 2);
    }

    /**
     * Scope: Empleados activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope: Empleados por tipo de contrato
     */
    public function scopePorTipoContrato($query, string $tipo)
    {
        return $query->where('tipo_contrato', $tipo);
    }

    /**
     * Scope: Empleados por centro de costo
     */
    public function scopePorCentroCosto($query, int $centroCostoId)
    {
        return $query->whereHas('centrosCostoActivos', function ($q) use ($centroCostoId) {
            $q->where('centro_costo_id', $centroCostoId);
        });
    }

    /**
     * Scope: Buscar empleados
     */
    public function scopeBuscar($query, string $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('numero_documento', 'like', "%{$termino}%")
              ->orWhere('codigo_empleado', 'like', "%{$termino}%")
              ->orWhere('primer_nombre', 'like', "%{$termino}%")
              ->orWhere('segundo_nombre', 'like', "%{$termino}%")
              ->orWhere('primer_apellido', 'like', "%{$termino}%")
              ->orWhere('segundo_apellido', 'like', "%{$termino}%")
              ->orWhere('email', 'like', "%{$termino}%");
        });
    }
}