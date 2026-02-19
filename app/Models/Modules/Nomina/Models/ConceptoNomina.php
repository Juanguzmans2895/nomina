<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Modules\Nomina\Enums\ClasificacionConcepto;
use App\Modules\Nomina\Enums\TipoConcepto;
use App\Models\User;

class ConceptoNomina extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'conceptos_nomina';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'clasificacion',
        'tipo',
        'base_salarial',
        'afecta_prestaciones',
        'afecta_seguridad_social',
        'afecta_parafiscales',
        'aplica_retencion',
        'porcentaje',
        'formula',
        'cuenta_debito_id',
        'cuenta_credito_id',
        'visible_colilla',
        'orden_colilla',
        'agrupador',
        'activo',
        'sistema',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'clasificacion' => ClasificacionConcepto::class,
        'tipo' => TipoConcepto::class,
        'base_salarial' => 'boolean',
        'afecta_prestaciones' => 'boolean',
        'afecta_seguridad_social' => 'boolean',
        'afecta_parafiscales' => 'boolean',
        'aplica_retencion' => 'boolean',
        'porcentaje' => 'decimal:2',
        'visible_colilla' => 'boolean',
        'orden_colilla' => 'integer',
        'activo' => 'boolean',
        'sistema' => 'boolean',
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
     * Relación con cuenta contable débito
     */
    public function cuentaDebito(): BelongsTo
    {
        return $this->belongsTo(CuentaContable::class, 'cuenta_debito_id');
    }

    /**
     * Relación con cuenta contable crédito
     */
    public function cuentaCredito(): BelongsTo
    {
        return $this->belongsTo(CuentaContable::class, 'cuenta_credito_id');
    }

    /**
     * Relación con empleados (conceptos fijos)
     */
    public function empleados(): BelongsToMany
    {
        return $this->belongsToMany(
            Empleado::class,
            'empleado_concepto_fijo',
            'concepto_nomina_id',
            'empleado_id'
        )
        ->withPivot('valor', 'porcentaje', 'cantidad', 'fecha_inicio', 'fecha_fin', 'activo', 'observaciones')
        ->withTimestamps();
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
     * Verificar si es devengado
     */
    public function esDevengado(): bool
    {
        return $this->clasificacion === ClasificacionConcepto::DEVENGADO;
    }

    /**
     * Verificar si es deducido
     */
    public function esDeducido(): bool
    {
        return $this->clasificacion === ClasificacionConcepto::DEDUCIDO;
    }

    /**
     * Verificar si es concepto fijo
     */
    public function esFijo(): bool
    {
        return $this->tipo === TipoConcepto::FIJO;
    }

    /**
     * Verificar si es novedad
     */
    public function esNovedad(): bool
    {
        return $this->tipo === TipoConcepto::NOVEDAD;
    }

    /**
     * Verificar si es calculado
     */
    public function esCalculado(): bool
    {
        return $this->tipo === TipoConcepto::CALCULADO;
    }

    /**
     * Verificar si es editable
     */
    public function esEditable(): bool
    {
        return !$this->sistema;
    }

    /**
     * Obtener el signo para cálculos
     */
    public function getSigno(): int
    {
        return $this->clasificacion->signo();
    }

    /**
     * Scope: Conceptos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope: Por clasificación
     */
    public function scopePorClasificacion($query, ClasificacionConcepto|string $clasificacion)
    {
        if (is_string($clasificacion)) {
            $clasificacion = ClasificacionConcepto::from($clasificacion);
        }
        return $query->where('clasificacion', $clasificacion);
    }

    /**
     * Scope: Por tipo
     */
    public function scopePorTipo($query, TipoConcepto|string $tipo)
    {
        if (is_string($tipo)) {
            $tipo = TipoConcepto::from($tipo);
        }
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope: Devengados
     */
    public function scopeDevengados($query)
    {
        return $query->where('clasificacion', ClasificacionConcepto::DEVENGADO);
    }

    /**
     * Scope: Deducidos
     */
    public function scopeDeducidos($query)
    {
        return $query->where('clasificacion', ClasificacionConcepto::DEDUCIDO);
    }

    /**
     * Scope: Conceptos fijos
     */
    public function scopeFijos($query)
    {
        return $query->where('tipo', TipoConcepto::FIJO);
    }

    /**
     * Scope: Novedades
     */
    public function scopeNovedades($query)
    {
        return $query->where('tipo', TipoConcepto::NOVEDAD);
    }

    /**
     * Scope: Calculados
     */
    public function scopeCalculados($query)
    {
        return $query->where('tipo', TipoConcepto::CALCULADO);
    }

    /**
     * Scope: Base salarial
     */
    public function scopeBaseSalarial($query)
    {
        return $query->where('base_salarial', true);
    }

    /**
     * Scope: Que afectan seguridad social
     */
    public function scopeAfectanSeguridadSocial($query)
    {
        return $query->where('afecta_seguridad_social', true);
    }

    /**
     * Scope: Que afectan parafiscales
     */
    public function scopeAfectanParafiscales($query)
    {
        return $query->where('afecta_parafiscales', true);
    }

    /**
     * Scope: Buscar conceptos
     */
    public function scopeBuscar($query, string $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('codigo', 'like', "%{$termino}%")
              ->orWhere('nombre', 'like', "%{$termino}%")
              ->orWhere('descripcion', 'like', "%{$termino}%");
        });
    }

    /**
     * Scope: Ordenar por orden de colilla
     */
    public function scopeOrdenColilla($query)
    {
        return $query->orderBy('orden_colilla')->orderBy('nombre');
    }
}