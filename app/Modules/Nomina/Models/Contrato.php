<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\User;

class Contrato extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'contratos';

    protected $fillable = [
        'numero_contrato',
        'tipo_contrato',
        'fecha_inicio',
        'fecha_fin',
        'plazo_meses',
        'plazo_dias',
        'tipo_documento_contratista',
        'numero_documento_contratista',
        'nombre_contratista',
        'email_contratista',
        'telefono_contratista',
        'direccion_contratista',
        'ciudad_contratista',
        'valor_total',
        'valor_mensual',
        'valor_pagado',
        'saldo_pendiente',
        'objeto',
        'obligaciones',
        'productos_entregables',
        'supervisor_id',
        'supervisor_nombre',
        'centro_costo_id',
        'aplica_retencion_fuente',
        'porcentaje_retencion_fuente',
        'aplica_retencion_ica',
        'porcentaje_retencion_ica',
        'aplica_estampilla',
        'porcentaje_estampilla',
        'banco',
        'tipo_cuenta',
        'numero_cuenta',
        'requiere_seguridad_social',
        'adjunta_planilla_pago',
        'requiere_poliza',
        'numero_poliza',
        'aseguradora',
        'fecha_vencimiento_poliza',
        'documentos_adjuntos',
        'estado',
        'observaciones',
        'observaciones_terminacion',
        'fecha_terminacion_real',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_vencimiento_poliza' => 'date',
        'fecha_terminacion_real' => 'date',
        'valor_total' => 'decimal:2',
        'valor_mensual' => 'decimal:2',
        'valor_pagado' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
        'porcentaje_retencion_fuente' => 'decimal:2',
        'porcentaje_retencion_ica' => 'decimal:2',
        'porcentaje_estampilla' => 'decimal:2',
        'aplica_retencion_fuente' => 'boolean',
        'aplica_retencion_ica' => 'boolean',
        'aplica_estampilla' => 'boolean',
        'requiere_seguridad_social' => 'boolean',
        'adjunta_planilla_pago' => 'boolean',
        'requiere_poliza' => 'boolean',
        'documentos_adjuntos' => 'array',
        'plazo_meses' => 'integer',
        'plazo_dias' => 'integer',
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
     * Relación con supervisor
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'supervisor_id');
    }

    /**
     * Relación con centro de costo
     */
    public function centroCosto(): BelongsTo
    {
        return $this->belongsTo(CentroCosto::class, 'centro_costo_id');
    }

    /**
     * Relación con pagos
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(PagoContrato::class, 'contrato_id');
    }

    /**
     * Pagos aprobados
     */
    public function pagosAprobados(): HasMany
    {
        return $this->pagos()->where('aprobado', true);
    }

    /**
     * Pagos realizados
     */
    public function pagosRealizados(): HasMany
    {
        return $this->pagos()->where('pagado', true);
    }

    /**
     * Relación con modificaciones
     */
    public function modificaciones(): HasMany
    {
        return $this->hasMany(ModificacionContrato::class, 'contrato_id');
    }

    /**
     * Modificaciones aprobadas
     */
    public function modificacionesAprobadas(): HasMany
    {
        return $this->modificaciones()->where('estado', 'aprobado');
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
     * Accessor: Días de contrato
     */
    public function getDiasContratoAttribute(): int
    {
        return $this->fecha_inicio->diffInDays($this->fecha_fin);
    }

    /**
     * Accessor: Días transcurridos
     */
    public function getDiasTranscurridosAttribute(): int
    {
        $hoy = now();
        if ($hoy < $this->fecha_inicio) return 0;
        if ($hoy > $this->fecha_fin) return $this->dias_contrato;
        return $this->fecha_inicio->diffInDays($hoy);
    }

    /**
     * Accessor: Días restantes
     */
    public function getDiasRestantesAttribute(): int
    {
        $hoy = now();
        if ($hoy < $this->fecha_inicio) return $this->dias_contrato;
        if ($hoy > $this->fecha_fin) return 0;
        return $hoy->diffInDays($this->fecha_fin);
    }

    /**
     * Accessor: Porcentaje de ejecución
     */
    public function getPorcentajeEjecucionAttribute(): float
    {
        if ($this->dias_contrato == 0) return 0;
        return round(($this->dias_transcurridos / $this->dias_contrato) * 100, 2);
    }

    /**
     * Accessor: Porcentaje de pago
     */
    public function getPorcentajePagoAttribute(): float
    {
        if ($this->valor_total == 0) return 0;
        return round(($this->valor_pagado / $this->valor_total) * 100, 2);
    }

    /**
     * Verificar si el contrato está activo
     */
    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }

    /**
     * Verificar si el contrato está vigente (dentro de fechas)
     */
    public function estaVigente(): bool
    {
        $hoy = now();
        return $hoy >= $this->fecha_inicio && $hoy <= $this->fecha_fin;
    }

    /**
     * Verificar si el contrato está vencido
     */
    public function estaVencido(): bool
    {
        return now() > $this->fecha_fin && $this->estado === 'activo';
    }

    /**
     * Verificar si requiere póliza vencida
     */
    public function tienePolizaVencida(): bool
    {
        if (!$this->requiere_poliza || !$this->fecha_vencimiento_poliza) {
            return false;
        }
        return now() > $this->fecha_vencimiento_poliza;
    }

    /**
     * Calcular valor neto de un pago
     */
    public function calcularValorNeto(float $valorBruto): array
    {
        $retencionFuente = $this->aplica_retencion_fuente 
            ? $valorBruto * ($this->porcentaje_retencion_fuente / 100)
            : 0;

        $retencionIca = $this->aplica_retencion_ica 
            ? $valorBruto * ($this->porcentaje_retencion_ica / 100)
            : 0;

        $estampilla = $this->aplica_estampilla 
            ? $valorBruto * ($this->porcentaje_estampilla / 100)
            : 0;

        $totalDeducciones = $retencionFuente + $retencionIca + $estampilla;
        $valorNeto = $valorBruto - $totalDeducciones;

        return [
            'valor_bruto' => $valorBruto,
            'retencion_fuente' => $retencionFuente,
            'retencion_ica' => $retencionIca,
            'estampilla' => $estampilla,
            'total_deducciones' => $totalDeducciones,
            'valor_neto' => $valorNeto,
        ];
    }

    /**
     * Actualizar saldo pendiente
     */
    public function actualizarSaldo(): void
    {
        $this->valor_pagado = $this->pagosRealizados()->sum('valor_neto');
        $this->saldo_pendiente = $this->valor_total - $this->valor_pagado;
        $this->save();
    }

    /**
     * Scope: Contratos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope: Contratos vigentes
     */
    public function scopeVigentes($query)
    {
        $hoy = now()->format('Y-m-d');
        return $query->where('fecha_inicio', '<=', $hoy)
                     ->where('fecha_fin', '>=', $hoy);
    }

    /**
     * Scope: Contratos próximos a vencer
     */
    public function scopeProximosVencer($query, int $dias = 30)
    {
        $fechaLimite = now()->addDays($dias)->format('Y-m-d');
        $hoy = now()->format('Y-m-d');
        return $query->where('estado', 'activo')
                     ->where('fecha_fin', '>=', $hoy)
                     ->where('fecha_fin', '<=', $fechaLimite);
    }

    /**
     * Scope: Por contratista
     */
    public function scopePorContratista($query, string $documento)
    {
        return $query->where('numero_documento_contratista', $documento);
    }

    /**
     * Scope: Por supervisor
     */
    public function scopePorSupervisor($query, int $supervisorId)
    {
        return $query->where('supervisor_id', $supervisorId);
    }

    /**
     * Scope: Por centro de costo
     */
    public function scopePorCentroCosto($query, int $centroCostoId)
    {
        return $query->where('centro_costo_id', $centroCostoId);
    }

    /**
     * Scope: Buscar contratos
     */
    public function scopeBuscar($query, string $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('numero_contrato', 'like', "%{$termino}%")
              ->orWhere('nombre_contratista', 'like', "%{$termino}%")
              ->orWhere('numero_documento_contratista', 'like', "%{$termino}%")
              ->orWhere('objeto', 'like', "%{$termino}%");
        });
    }
}