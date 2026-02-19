<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NominaConcepto extends Model
{
    use HasFactory;

    protected $table = 'nomina_conceptos';

    protected $fillable = [
        'nomina_detalle_id',
        'concepto_nomina_id',
        'novedad_nomina_id',
        'codigo_concepto',
        'nombre_concepto',
        'clasificacion',
        'cantidad',
        'valor_unitario',
        'porcentaje',
        'valor',
        'formula_calculo',
        'observaciones',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'valor_unitario' => 'decimal:2',
        'porcentaje' => 'decimal:2',
        'valor' => 'decimal:2',
    ];

    /**
     * Relación con detalle de nómina
     */
    public function nominaDetalle(): BelongsTo
    {
        return $this->belongsTo(NominaDetalle::class, 'nomina_detalle_id');
    }

    /**
     * Relación con concepto de nómina
     */
    public function concepto(): BelongsTo
    {
        return $this->belongsTo(ConceptoNomina::class, 'concepto_nomina_id');
    }

    /**
     * Relación con novedad
     */
    public function novedad(): BelongsTo
    {
        return $this->belongsTo(NovedadNomina::class, 'novedad_nomina_id');
    }

    /**
     * Verificar si es devengado
     */
    public function esDevengado(): bool
    {
        return $this->clasificacion === 'devengado';
    }

    /**
     * Verificar si es deducido
     */
    public function esDeducido(): bool
    {
        return $this->clasificacion === 'deducido';
    }

    /**
     * Scopes
     */
    public function scopeDevengados($query)
    {
        return $query->where('clasificacion', 'devengado');
    }

    public function scopeDeducidos($query)
    {
        return $query->where('clasificacion', 'deducido');
    }

    public function scopePorConcepto($query, string $codigo)
    {
        return $query->where('codigo_concepto', $codigo);
    }
}