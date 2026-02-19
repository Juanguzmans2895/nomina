<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class TipoNomina extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipos_nomina';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'tipo',
        'requiere_seguridad_social',
        'requiere_parafiscales',
        'requiere_provisiones',
        'activo',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'requiere_seguridad_social' => 'boolean',
        'requiere_parafiscales' => 'boolean',
        'requiere_provisiones' => 'boolean',
        'activo' => 'boolean',
    ];

    /**
     * Relación con nóminas
     */
    public function nominas(): HasMany
    {
        return $this->hasMany(Nomina::class, 'tipo_nomina_id');
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
     * Scope: Tipos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}