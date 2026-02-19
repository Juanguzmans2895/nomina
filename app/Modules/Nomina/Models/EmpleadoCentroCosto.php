<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use app\Models\User;
use Carbon\Carbon;

class EmpleadoCentroCosto extends Model
{
    use SoftDeletes;

    protected $table = 'empleado_centros_costo';

    protected $fillable = [
        'empleado_id',
        'centro_costo_id',
        'porcentaje',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'observaciones',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'porcentaje' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
    ];

    protected $dates = [
        'fecha_inicio',
        'fecha_fin',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Relación con Empleado
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    /**
     * Relación con Centro de Costo
     */
    public function centroCosto(): BelongsTo
    {
        return $this->belongsTo(CentroCosto::class, 'centro_costo_id');
    }

    /**
     * Relación con usuario que creó
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación con usuario que actualizó
     */
    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope para asignaciones activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true)
                    ->where(function($q) {
                        $q->whereNull('fecha_fin')
                          ->orWhere('fecha_fin', '>=', now());
                    });
    }

    /**
     * Scope para asignaciones inactivas
     */
    public function scopeInactivas($query)
    {
        return $query->where('activo', false)
                    ->orWhere(function($q) {
                        $q->whereNotNull('fecha_fin')
                          ->where('fecha_fin', '<', now());
                    });
    }

    /**
     * Scope para asignaciones de un empleado
     */
    public function scopeDelEmpleado($query, $empleadoId)
    {
        return $query->where('empleado_id', $empleadoId);
    }

    /**
     * Scope para asignaciones de un centro de costo
     */
    public function scopeDelCentroCosto($query, $centroCostoId)
    {
        return $query->where('centro_costo_id', $centroCostoId);
    }

    /**
     * Scope para asignaciones en una fecha específica
     */
    public function scopeEnFecha($query, $fecha)
    {
        $fecha = Carbon::parse($fecha);
        
        return $query->where('fecha_inicio', '<=', $fecha)
                    ->where(function($q) use ($fecha) {
                        $q->whereNull('fecha_fin')
                          ->orWhere('fecha_fin', '>=', $fecha);
                    });
    }

    /**
     * Verificar si la asignación está vigente
     */
    public function getVigenteAttribute(): bool
    {
        if (!$this->activo) {
            return false;
        }

        $ahora = now();

        if ($this->fecha_inicio > $ahora) {
            return false;
        }

        if ($this->fecha_fin && $this->fecha_fin < $ahora) {
            return false;
        }

        return true;
    }

    /**
     * Obtener el nombre completo del centro de costo
     */
    public function getNombreCentroAttribute(): string
    {
        return $this->centroCosto 
            ? "{$this->centroCosto->codigo} - {$this->centroCosto->nombre}"
            : '';
    }

    /**
     * Calcular días de vigencia
     */
    public function getDiasVigenciaAttribute(): ?int
    {
        if (!$this->fecha_fin) {
            return null;
        }

        return $this->fecha_inicio->diffInDays($this->fecha_fin);
    }

    /**
     * Calcular días restantes
     */
    public function getDiasRestantesAttribute(): ?int
    {
        if (!$this->fecha_fin) {
            return null;
        }

        $ahora = now();

        if ($this->fecha_fin < $ahora) {
            return 0;
        }

        return $ahora->diffInDays($this->fecha_fin);
    }

    /**
     * Verificar si la asignación está próxima a vencer (30 días)
     */
    public function getProximaAVencerAttribute(): bool
    {
        if (!$this->fecha_fin || !$this->vigente) {
            return false;
        }

        return $this->dias_restantes <= 30 && $this->dias_restantes > 0;
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Al crear, establecer created_by
        static::creating(function ($model) {
            if (auth()->check() && !$model->created_by) {
                $model->created_by = auth()->id();
            }
        });

        // Al actualizar, establecer updated_by
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        // Al eliminar, desactivar si está activo
        static::deleting(function ($model) {
            if ($model->activo) {
                $model->activo = false;
                $model->fecha_fin = now();
                $model->save();
            }
        });
    }

    /**
     * Validar que la suma de porcentajes no exceda 100%
     */
    public static function validarPorcentajes($empleadoId, $porcentajeNuevo, $excepto = null): bool
    {
        $query = self::activas()
            ->delEmpleado($empleadoId)
            ->enFecha(now());

        if ($excepto) {
            $query->where('id', '!=', $excepto);
        }

        $sumaActual = $query->sum('porcentaje');
        $total = $sumaActual + $porcentajeNuevo;

        return $total <= 100.00;
    }

    /**
     * Obtener porcentaje disponible para un empleado
     */
    public static function porcentajeDisponible($empleadoId, $excepto = null): float
    {
        $query = self::activas()
            ->delEmpleado($empleadoId)
            ->enFecha(now());

        if ($excepto) {
            $query->where('id', '!=', $excepto);
        }

        $asignado = $query->sum('porcentaje');

        return 100.00 - $asignado;
    }

    /**
     * Desactivar asignación
     */
    public function desactivar(Carbon $fechaFin = null): bool
    {
        $this->activo = false;
        $this->fecha_fin = $fechaFin ?? now();
        
        return $this->save();
    }

    /**
     * Activar asignación
     */
    public function activar(): bool
    {
        $this->activo = true;
        $this->fecha_fin = null;
        
        return $this->save();
    }
}