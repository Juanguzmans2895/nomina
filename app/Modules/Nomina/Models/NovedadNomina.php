<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\User;

class NovedadNomina extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'novedades_nomina';

    protected $fillable = [
        'empleado_id',
        'concepto_nomina_id',
        'periodo_nomina_id',
        'nomina_id',
        'fecha_novedad',
        'fecha_inicio',
        'fecha_fin',
        'cantidad',
        'valor_unitario',
        'porcentaje',
        'valor_total',
        'descripcion',
        'observaciones',
        'referencia',
        'estado',
        'procesada',
        'requiere_aprobacion',
        'aprobado_by',
        'fecha_aprobacion',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_novedad' => 'date',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_aprobacion' => 'datetime',
        'cantidad' => 'decimal:2',
        'valor_unitario' => 'decimal:2',
        'porcentaje' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'procesada' => 'boolean',
        'requiere_aprobacion' => 'boolean',
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
     * Relación con empleado
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    /**
     * Relación con concepto
     */
    public function concepto(): BelongsTo
    {
        return $this->belongsTo(ConceptoNomina::class, 'concepto_nomina_id');
    }

    /**
     * Relación con período
     */
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoNomina::class, 'periodo_nomina_id');
    }

    /**
     * Relación con nómina
     */
    public function nomina(): BelongsTo
    {
        return $this->belongsTo(Nomina::class, 'nomina_id');
    }

    /**
     * Usuario que aprobó
     */
    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_by');
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
     * Aprobar novedad
     */
    public function aprobar(User $usuario): bool
    {
        $this->aprobado_by = $usuario->id;
        $this->fecha_aprobacion = now();
        return $this->save();
    }

    /**
     * Rechazar novedad
     */
    public function rechazar(): bool
    {
        $this->estado = 'rechazada';
        return $this->save();
    }

    /**
     * Anular novedad
     */
    public function anular(): bool
    {
        $this->estado = 'anulada';
        return $this->save();
    }

    /**
     * Scopes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeProcesadas($query)
    {
        return $query->where('procesada', true);
    }

    public function scopePorPeriodo($query, int $periodoId)
    {
        return $query->where('periodo_nomina_id', $periodoId);
    }

    public function scopePorEmpleado($query, int $empleadoId)
    {
        return $query->where('empleado_id', $empleadoId);
    }

    public function scopeRequierenAprobacion($query)
    {
        return $query->where('requiere_aprobacion', true)
                     ->whereNull('aprobado_by');
    }
}