<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class AsientoContableNomina extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'asientos_contables_nomina';

    protected $fillable = [
        'numero_asiento',
        'fecha_asiento',
        'periodo_contable',
        'descripcion',
        'nomina_id',
        'tipo_asiento',
        'total_debito',
        'total_credito',
        'diferencia',
        'estado',
        'cuadrado',
        'aprobado_by',
        'fecha_aprobacion',
        'contabilizado_by',
        'fecha_contabilizacion',
        'observaciones',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_asiento' => 'date',
        'fecha_aprobacion' => 'datetime',
        'fecha_contabilizacion' => 'datetime',
        'total_debito' => 'decimal:2',
        'total_credito' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'cuadrado' => 'boolean',
    ];

    /**
     * Relación con nómina
     */
    public function nomina(): BelongsTo
    {
        return $this->belongsTo(Nomina::class, 'nomina_id');
    }

    /**
     * Relación con detalles
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleAsientoNomina::class, 'asiento_id');
    }

    /**
     * Usuarios
     */
    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_by');
    }

    public function contabilizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contabilizado_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Calcular totales
     */
    public function calcularTotales(): void
    {
        $this->total_debito = $this->detalles()->sum('debito');
        $this->total_credito = $this->detalles()->sum('credito');
        $this->diferencia = $this->total_debito - $this->total_credito;
        $this->cuadrado = abs($this->diferencia) < 0.01; // Tolerancia de 1 centavo
        $this->save();
    }

    /**
     * Aprobar asiento
     */
    public function aprobar(User $usuario): bool
    {
        if (!$this->cuadrado) {
            return false;
        }

        $this->estado = 'aprobado';
        $this->aprobado_by = $usuario->id;
        $this->fecha_aprobacion = now();
        return $this->save();
    }

    /**
     * Contabilizar asiento
     */
    public function contabilizar(User $usuario): bool
    {
        if ($this->estado !== 'aprobado') {
            return false;
        }

        $this->estado = 'contabilizado';
        $this->contabilizado_by = $usuario->id;
        $this->fecha_contabilizacion = now();
        return $this->save();
    }

    /**
     * Anular asiento
     */
    public function anular(): bool
    {
        if ($this->estado === 'contabilizado') {
            return false;
        }

        $this->estado = 'anulado';
        return $this->save();
    }

    /**
     * Scopes
     */
    public function scopePorNomina($query, int $nominaId)
    {
        return $query->where('nomina_id', $nominaId);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_asiento', $tipo);
    }

    public function scopeBorradores($query)
    {
        return $query->where('estado', 'borrador');
    }

    public function scopeAprobados($query)
    {
        return $query->where('estado', 'aprobado');
    }

    public function scopeContabilizados($query)
    {
        return $query->where('estado', 'contabilizado');
    }

    public function scopeCuadrados($query)
    {
        return $query->where('cuadrado', true);
    }

    public function scopeDescuadrados($query)
    {
        return $query->where('cuadrado', false);
    }
}