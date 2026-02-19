<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\User;

class PagoContrato extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'pagos_contratos';

    protected $fillable = [
        'contrato_id',
        'numero_pago',
        'numero_cuota',
        'fecha_pago',
        'periodo_inicio',
        'periodo_fin',
        'valor_bruto',
        'retencion_fuente',
        'retencion_ica',
        'estampilla',
        'otras_deducciones',
        'valor_neto',
        'numero_acta',
        'fecha_acta',
        'descripcion_actividades',
        'porcentaje_avance',
        'aprobado',
        'aprobado_by',
        'fecha_aprobacion',
        'pagado',
        'comprobante_egreso',
        'fecha_pago_real',
        'medio_pago',
        'contabilizado',
        'numero_asiento',
        'fecha_contabilizacion',
        'adjunta_informe',
        'adjunta_factura',
        'adjunta_planilla_ss',
        'documentos_adjuntos',
        'observaciones',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'periodo_inicio' => 'date',
        'periodo_fin' => 'date',
        'fecha_acta' => 'date',
        'fecha_aprobacion' => 'datetime',
        'fecha_pago_real' => 'date',
        'fecha_contabilizacion' => 'date',
        'valor_bruto' => 'decimal:2',
        'retencion_fuente' => 'decimal:2',
        'retencion_ica' => 'decimal:2',
        'estampilla' => 'decimal:2',
        'otras_deducciones' => 'decimal:2',
        'valor_neto' => 'decimal:2',
        'numero_cuota' => 'integer',
        'porcentaje_avance' => 'integer',
        'aprobado' => 'boolean',
        'pagado' => 'boolean',
        'contabilizado' => 'boolean',
        'adjunta_informe' => 'boolean',
        'adjunta_factura' => 'boolean',
        'adjunta_planilla_ss' => 'boolean',
        'documentos_adjuntos' => 'array',
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
     * Relación con contrato
     */
    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
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
     * Accessor: Total de deducciones
     */
    public function getTotalDeduccionesAttribute(): float
    {
        return $this->retencion_fuente + $this->retencion_ica + 
               $this->estampilla + $this->otras_deducciones;
    }

    /**
     * Aprobar el pago
     */
    public function aprobar(User $usuario): bool
    {
        $this->aprobado = true;
        $this->aprobado_by = $usuario->id;
        $this->fecha_aprobacion = now();
        return $this->save();
    }

    /**
     * Registrar el pago
     */
    public function registrarPago(array $datos): bool
    {
        $this->pagado = true;
        $this->comprobante_egreso = $datos['comprobante_egreso'] ?? null;
        $this->fecha_pago_real = $datos['fecha_pago_real'] ?? now();
        $this->medio_pago = $datos['medio_pago'] ?? null;
        
        $guardado = $this->save();
        
        if ($guardado) {
            $this->contrato->actualizarSaldo();
        }
        
        return $guardado;
    }

    /**
     * Contabilizar el pago
     */
    public function contabilizar(string $numeroAsiento): bool
    {
        $this->contabilizado = true;
        $this->numero_asiento = $numeroAsiento;
        $this->fecha_contabilizacion = now();
        return $this->save();
    }

    /**
     * Verificar si el pago está completo
     */
    public function estaCompleto(): bool
    {
        return $this->aprobado && $this->pagado && $this->contabilizado;
    }

    /**
     * Scope: Pagos aprobados
     */
    public function scopeAprobados($query)
    {
        return $query->where('aprobado', true);
    }

    /**
     * Scope: Pagos realizados
     */
    public function scopePagados($query)
    {
        return $query->where('pagado', true);
    }

    /**
     * Scope: Pagos pendientes de aprobación
     */
    public function scopePendientesAprobacion($query)
    {
        return $query->where('aprobado', false);
    }

    /**
     * Scope: Pagos pendientes de pago
     */
    public function scopePendientesPago($query)
    {
        return $query->where('aprobado', true)->where('pagado', false);
    }

    /**
     * Scope: Por contrato
     */
    public function scopePorContrato($query, int $contratoId)
    {
        return $query->where('contrato_id', $contratoId);
    }

    /**
     * Scope: Por período
     */
    public function scopePorPeriodo($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);
    }
}