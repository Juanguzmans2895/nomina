<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class RubroPresupuestal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rubros_presupuestales';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'padre_id',
        'nivel',
        'valor_aprobado',
        'valor_disponible',
        'activo',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'nivel' => 'integer',
        'valor_aprobado' => 'decimal:2',
        'valor_disponible' => 'decimal:2',
        'activo' => 'boolean',
    ];

    /**
     * Rubro padre
     */
    public function padre(): BelongsTo
    {
        return $this->belongsTo(RubroPresupuestal::class, 'padre_id');
    }

    /**
     * Rubros hijos
     */
    public function hijos(): HasMany
    {
        return $this->hasMany(RubroPresupuestal::class, 'padre_id');
    }

    /**
     * Centros de costo asociados
     */
    public function centrosCosto(): HasMany
    {
        return $this->hasMany(CentroCosto::class, 'rubro_presupuestal_id');
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
     * Scope: Rubros activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}