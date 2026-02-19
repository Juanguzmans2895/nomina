<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class PeriodoNomina extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'periodos_nomina';

    protected $fillable = [
        'codigo',
        'nombre',
        'anio',
        'mes',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'tipo_nomina_id',
        'activo',
        'observaciones',
        'created_by',
        'updated_by',
        'cerrado_by',
        'fecha_cierre',
    ];

    protected $casts = [
        'anio' => 'integer',
        'mes' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_cierre' => 'datetime',
        'activo' => 'boolean',
    ];

    /**
     * Relación con tipo de nómina
     */
    public function tipoNomina(): BelongsTo
    {
        return $this->belongsTo(TipoNomina::class, 'tipo_nomina_id');
    }

    /**
     * Relación con nóminas
     */
    public function nominas(): HasMany
    {
        return $this->hasMany(Nomina::class, 'periodo_nomina_id');
    }

    /**
     * Relación con novedades
     */
    public function novedades(): HasMany
    {
        return $this->hasMany(NovedadNomina::class, 'periodo_nomina_id');
    }

    /**
     * Usuario que cerró
     */
    public function cerradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrado_by');
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
     * Cerrar período
     */
    public function cerrar(User $usuario = null): bool
    {
        $this->estado = 'cerrado';
        $this->fecha_cierre = now();
        $this->cerrado_by = $usuario ? $usuario->id : auth()->id();
        return $this->save();
    }

    /**
     * Reabrir período
     */
    public function reabrir(): bool
    {
        $this->estado = 'abierto';
        $this->fecha_cierre = null;
        $this->cerrado_by = null;
        return $this->save();
    }

    /**
     * Verificar si está abierto
     */
    public function estaAbierto(): bool
    {
        return $this->estado === 'abierto';
    }

    /**
     * Verificar si está cerrado
     */
    public function estaCerrado(): bool
    {
        return $this->estado === 'cerrado';
    }

    /**
     * Verificar si está bloqueado
     */
    public function estaBloqueado(): bool
    {
        return $this->estado === 'bloqueado';
    }

    /**
     * Accessor: Nombre del mes
     */
    public function getNombreMesAttribute(): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
            4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        
        return $meses[$this->mes] ?? '';
    }

    /**
     * Scopes
     */
    public function scopeAbiertos($query)
    {
        return $query->where('estado', 'abierto'); // ✅ Usar 'estado' en lugar de 'cerrado'
    }

    public function scopeCerrados($query)
    {
        return $query->where('estado', 'cerrado'); // ✅ Usar 'estado' en lugar de 'cerrado'
    }

    public function scopeBloqueados($query)
    {
        return $query->where('estado', 'bloqueado');
    }

    public function scopePorAnio($query, int $anio)
    {
        return $query->where('anio', $anio);
    }

    public function scopePorMes($query, int $mes)
    {
        return $query->where('mes', $mes);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}