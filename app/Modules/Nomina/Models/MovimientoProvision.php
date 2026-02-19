<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class MovimientoProvision extends Model
{
    use HasFactory;

    protected $table = 'movimientos_provisiones';

    protected $fillable = [
        'empleado_id',
        'tipo_provision',
        'tipo_movimiento',
        'valor',
        'fecha_movimiento',
        'numero_documento',
        'descripcion',
        'observaciones',
        'nomina_id',
        'contabilizado',
        'numero_asiento',
        'fecha_contabilizacion',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_movimiento' => 'date',
        'fecha_contabilizacion' => 'date',
        'valor' => 'decimal:2',
        'contabilizado' => 'boolean',
    ];

    /**
     * Relación con empleado
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    /**
     * Relación con nómina
     */
    public function nomina(): BelongsTo
    {
        return $this->belongsTo(Nomina::class, 'nomina_id');
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
     * Verificar si es causación
     */
    public function esCausacion(): bool
    {
        return $this->tipo_movimiento === 'causacion';
    }

    /**
     * Verificar si es pago
     */
    public function esPago(): bool
    {
        return $this->tipo_movimiento === 'pago';
    }

    /**
     * Contabilizar movimiento
     */
    public function contabilizar(string $numeroAsiento): bool
    {
        $this->contabilizado = true;
        $this->numero_asiento = $numeroAsiento;
        $this->fecha_contabilizacion = now();
        return $this->save();
    }

    /**
     * Scopes
     */
    public function scopePorEmpleado($query, int $empleadoId)
    {
        return $query->where('empleado_id', $empleadoId);
    }

    public function scopePorTipoProvision($query, string $tipo)
    {
        return $query->where('tipo_provision', $tipo);
    }

    public function scopePorTipoMovimiento($query, string $tipo)
    {
        return $query->where('tipo_movimiento', $tipo);
    }

    public function scopeCausaciones($query)
    {
        return $query->where('tipo_movimiento', 'causacion');
    }

    public function scopePagos($query)
    {
        return $query->where('tipo_movimiento', 'pago');
    }

    public function scopeContabilizados($query)
    {
        return $query->where('contabilizado', true);
    }

    public function scopePendientesContabilizar($query)
    {
        return $query->where('contabilizado', false);
    }
}