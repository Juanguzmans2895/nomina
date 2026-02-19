<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\User;

class ModificacionContrato extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'modificaciones_contratos';

    protected $fillable = [
        'contrato_id',
        'tipo_modificacion',
        'numero_modificacion',
        'fecha_modificacion',
        'justificacion',
        'valor_adicion',
        'nuevo_valor_total',
        'dias_prorroga',
        'nueva_fecha_fin',
        'fecha_suspension',
        'fecha_reinicio',
        'dias_suspension',
        'descripcion_modificacion',
        'documentos_adjuntos',
        'estado',
        'observaciones',
        'created_by',
        'updated_by',
        'aprobado_by',
        'fecha_aprobacion',
    ];

    protected $casts = [
        'fecha_modificacion' => 'date',
        'nueva_fecha_fin' => 'date',
        'fecha_suspension' => 'date',
        'fecha_reinicio' => 'date',
        'fecha_aprobacion' => 'datetime',
        'valor_adicion' => 'decimal:2',
        'nuevo_valor_total' => 'decimal:2',
        'dias_prorroga' => 'integer',
        'dias_suspension' => 'integer',
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
     * Usuario que aprobó
     */
    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_by');
    }

    /**
     * Verificar si es una adición
     */
    public function esAdicion(): bool
    {
        return $this->tipo_modificacion === 'adicion';
    }

    /**
     * Verificar si es una prórroga
     */
    public function esProrroga(): bool
    {
        return $this->tipo_modificacion === 'prorroga';
    }

    /**
     * Verificar si es una suspensión
     */
    public function esSuspension(): bool
    {
        return $this->tipo_modificacion === 'suspension';
    }

    /**
     * Aprobar modificación
     */
    public function aprobar(User $usuario): bool
    {
        $this->estado = 'aprobado';
        $this->aprobado_by = $usuario->id;
        $this->fecha_aprobacion = now();
        
        if (!$this->save()) {
            return false;
        }

        // Aplicar cambios al contrato
        return $this->aplicarAlContrato();
    }

    /**
     * Aplicar modificación al contrato
     */
    protected function aplicarAlContrato(): bool
    {
        $contrato = $this->contrato;

        switch ($this->tipo_modificacion) {
            case 'adicion':
                $contrato->valor_total = $this->nuevo_valor_total;
                $contrato->saldo_pendiente += $this->valor_adicion;
                break;

            case 'prorroga':
                $contrato->fecha_fin = $this->nueva_fecha_fin;
                $contrato->plazo_dias += $this->dias_prorroga;
                break;

            case 'suspension':
                $contrato->estado = 'suspendido';
                break;

            case 'reinicio':
                $contrato->estado = 'activo';
                // Ajustar fecha fin por los días de suspensión
                if ($this->dias_suspension) {
                    $contrato->fecha_fin = $contrato->fecha_fin->addDays($this->dias_suspension);
                }
                break;
        }

        return $contrato->save();
    }

    /**
     * Rechazar modificación
     */
    public function rechazar(string $observaciones = null): bool
    {
        $this->estado = 'rechazado';
        if ($observaciones) {
            $this->observaciones = $observaciones;
        }
        return $this->save();
    }

    /**
     * Scope: Modificaciones aprobadas
     */
    public function scopeAprobadas($query)
    {
        return $query->where('estado', 'aprobado');
    }

    /**
     * Scope: Por tipo
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_modificacion', $tipo);
    }

    /**
     * Scope: Adiciones
     */
    public function scopeAdiciones($query)
    {
        return $query->where('tipo_modificacion', 'adicion');
    }

    /**
     * Scope: Prórrogas
     */
    public function scopeProrrogas($query)
    {
        return $query->where('tipo_modificacion', 'prorroga');
    }
}