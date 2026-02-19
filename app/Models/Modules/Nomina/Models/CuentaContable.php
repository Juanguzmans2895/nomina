<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\User;

class CuentaContable extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'cuentas_contables';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'tipo',
        'naturaleza',
        'nivel',
        'cuenta_padre_id',
        'maneja_tercero',
        'maneja_centro_costo',
        'activa',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'nivel' => 'integer',
        'maneja_tercero' => 'boolean',
        'maneja_centro_costo' => 'boolean',
        'activa' => 'boolean',
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
     * Cuenta padre
     */
    public function cuentaPadre(): BelongsTo
    {
        return $this->belongsTo(CuentaContable::class, 'cuenta_padre_id');
    }

    /**
     * Cuentas hijas
     */
    public function cuentasHijas(): HasMany
    {
        return $this->hasMany(CuentaContable::class, 'cuenta_padre_id');
    }

    /**
     * Usuario que creó
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuario que actualizó
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope: Cuentas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Scope: Por tipo
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope: Por naturaleza
     */
    public function scopePorNaturaleza($query, string $naturaleza)
    {
        return $query->where('naturaleza', $naturaleza);
    }
}