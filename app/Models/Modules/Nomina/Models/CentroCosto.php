<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\User;

class CentroCosto extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'centros_costo';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'padre_id',
        'activo',
        'codigo_presupuestal',
        'rubro_presupuestal_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'activo' => 'boolean',
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

    // Relación con padre
    public function padre()
    {
        return $this->belongsTo(CentroCosto::class, 'padre_id');
    }

    // Relación con hijos
    public function hijos()
    {
        return $this->hasMany(CentroCosto::class, 'padre_id');
    }

    // Relación con empleados
    public function empleados()
    {
        return $this->belongsToMany(Empleado::class, 'empleado_centro_costo');
    }

    /**
     * Empleados activos
     */
    public function empleadosActivos(): BelongsToMany
    {
        return $this->empleados()
            ->wherePivot('activo', true)
            ->where('empleados.estado', 'activo');
    }

    /**
     * Relación con rubro presupuestal
     */
    public function rubroPresupuestal(): BelongsTo
    {
        return $this->belongsTo(RubroPresupuestal::class, 'rubro_presupuestal_id');
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
     * Accessor: Nombre completo con jerarquía
     */
    public function getNombreCompletoAttribute(): string
    {
        if ($this->padre) {
            return $this->padre->nombre_completo . ' > ' . $this->nombre;
        }
        return $this->nombre;
    }

    /**
     * Accessor: Código completo con jerarquía
     */
    public function getCodigoCompletoAttribute(): string
    {
        if ($this->padre) {
            return $this->padre->codigo_completo . '.' . $this->codigo;
        }
        return $this->codigo;
    }

    /**
     * Obtener todos los centros hijos (recursivo)
     */
    public function todosLosHijos()
    {
        return $this->hijos()->with('todosLosHijos');
    }

    /**
     * Verificar si tiene empleados asignados
     */
    public function tieneEmpleados(): bool
    {
        return $this->empleados()->exists();
    }

    /**
     * Scope: Centros activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Centros raíz (sin padre)
     */
    public function scopeRaiz($query)
    {
        return $query->whereNull('padre_id');
    }

    /**
     * Scope: Buscar centros
     */
    public function scopeBuscar($query, string $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('codigo', 'like', "%{$termino}%")
              ->orWhere('nombre', 'like', "%{$termino}%")
              ->orWhere('codigo_presupuestal', 'like', "%{$termino}%");
        });
    }
}