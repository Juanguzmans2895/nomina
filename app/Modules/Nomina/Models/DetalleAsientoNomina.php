<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleAsientoNomina extends Model
{
    use HasFactory;

    protected $table = 'detalles_asientos_nomina';

    protected $fillable = [
        'asiento_id',
        'cuenta_contable_id',
        'codigo_cuenta',
        'nombre_cuenta',
        'empleado_id',
        'documento_tercero',
        'nombre_tercero',
        'centro_costo_id',
        'codigo_centro_costo',
        'debito',
        'credito',
        'base',
        'porcentaje',
        'descripcion',
        'orden',
    ];

    protected $casts = [
        'debito' => 'decimal:2',
        'credito' => 'decimal:2',
        'base' => 'decimal:2',
        'porcentaje' => 'decimal:2',
        'orden' => 'integer',
    ];

    /**
     * Relación con asiento
     */
    public function asiento(): BelongsTo
    {
        return $this->belongsTo(AsientoContableNomina::class, 'asiento_id');
    }

    /**
     * Relación con cuenta contable
     */
    public function cuentaContable(): BelongsTo
    {
        return $this->belongsTo(CuentaContable::class, 'cuenta_contable_id');
    }

    /**
     * Relación con empleado
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    /**
     * Relación con centro de costo
     */
    public function centroCosto(): BelongsTo
    {
        return $this->belongsTo(CentroCosto::class, 'centro_costo_id');
    }

    /**
     * Accessor: Saldo (débito - crédito)
     */
    public function getSaldoAttribute(): float
    {
        return $this->debito - $this->credito;
    }

    /**
     * Scopes
     */
    public function scopeDebitos($query)
    {
        return $query->where('debito', '>', 0);
    }

    public function scopeCreditos($query)
    {
        return $query->where('credito', '>', 0);
    }

    public function scopePorCuenta($query, string $codigoCuenta)
    {
        return $query->where('codigo_cuenta', $codigoCuenta);
    }
}