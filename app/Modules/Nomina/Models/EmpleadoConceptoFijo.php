<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use app\Models\User;
use Carbon\Carbon;

class EmpleadoConceptoFijo extends Model
{
    use SoftDeletes;

    protected $table = 'empleado_conceptos_fijos';

    protected $fillable = [
        'empleado_id',
        'concepto_nomina_id',
        'valor',
        'porcentaje',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'observaciones',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
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
     * Relación con Concepto de Nómina
     */
    public function concepto(): BelongsTo
    {
        return $this->belongsTo(ConceptoNomina::class, 'concepto_nomina_id');
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
     * Scope para conceptos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true)
                    ->where(function($q) {
                        $q->whereNull('fecha_fin')
                          ->orWhere('fecha_fin', '>=', now());
                    });
    }

    /**
     * Scope para conceptos inactivos
     */
    public function scopeInactivos($query)
    {
        return $query->where('activo', false)
                    ->orWhere(function($q) {
                        $q->whereNotNull('fecha_fin')
                          ->where('fecha_fin', '<', now());
                    });
    }

    /**
     * Scope para conceptos de un empleado
     */
    public function scopeDelEmpleado($query, $empleadoId)
    {
        return $query->where('empleado_id', $empleadoId);
    }

    /**
     * Scope para conceptos por clasificación
     */
    public function scopePorClasificacion($query, $clasificacion)
    {
        return $query->whereHas('concepto', function($q) use ($clasificacion) {
            $q->where('clasificacion', $clasificacion);
        });
    }

    /**
     * Scope para devengados
     */
    public function scopeDevengados($query)
    {
        return $query->porClasificacion('devengado');
    }

    /**
     * Scope para deducciones
     */
    public function scopeDeducciones($query)
    {
        return $query->porClasificacion('deduccion');
    }

    /**
     * Scope para conceptos vigentes en una fecha
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
     * Verificar si el concepto está vigente
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
     * Obtener el nombre completo del concepto
     */
    public function getNombreConceptoAttribute(): string
    {
        return $this->concepto 
            ? "{$this->concepto->codigo} - {$this->concepto->nombre}"
            : '';
    }

    /**
     * Verificar si es cálculo por valor fijo
     */
    public function getEsValorFijoAttribute(): bool
    {
        return $this->valor !== null && $this->valor > 0;
    }

    /**
     * Verificar si es cálculo por porcentaje
     */
    public function getEsPorcentajeAttribute(): bool
    {
        return $this->porcentaje !== null && $this->porcentaje > 0;
    }

    /**
     * Calcular el valor a aplicar según el salario
     */
    public function calcularValor($salarioBase): float
    {
        if ($this->es_valor_fijo) {
            return $this->valor;
        }

        if ($this->es_porcentaje) {
            return $salarioBase * ($this->porcentaje / 100);
        }

        return 0;
    }

    /**
     * Obtener texto de cálculo
     */
    public function getTextoCalculoAttribute(): string
    {
        if ($this->es_valor_fijo) {
            return 'Valor Fijo: $' . number_format($this->valor, 0);
        }

        if ($this->es_porcentaje) {
            return 'Porcentaje: ' . number_format($this->porcentaje, 2) . '%';
        }

        return 'Sin configurar';
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
     * Verificar si está próximo a vencer (30 días)
     */
    public function getProximoAVencerAttribute(): bool
    {
        if (!$this->fecha_fin || !$this->vigente) {
            return false;
        }

        return $this->dias_restantes <= 30 && $this->dias_restantes > 0;
    }

    /**
     * Desactivar concepto
     */
    public function desactivar(Carbon $fechaFin = null): bool
    {
        $this->activo = false;
        $this->fecha_fin = $fechaFin ?? now();
        
        return $this->save();
    }

    /**
     * Activar concepto
     */
    public function activar(): bool
    {
        $this->activo = true;
        $this->fecha_fin = null;
        
        return $this->save();
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

            // Validar que tenga valor o porcentaje
            if (!$model->valor && !$model->porcentaje) {
                throw new \Exception('Debe especificar un valor fijo o un porcentaje');
            }

            // No puede tener ambos
            if ($model->valor && $model->porcentaje) {
                throw new \Exception('No puede tener valor fijo y porcentaje al mismo tiempo');
            }
        });

        // Al actualizar, establecer updated_by
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }

            // Validar que tenga valor o porcentaje
            if (!$model->valor && !$model->porcentaje) {
                throw new \Exception('Debe especificar un valor fijo o un porcentaje');
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
     * Validar que no exista concepto duplicado activo
     */
    public static function validarDuplicado($empleadoId, $conceptoId, $excepto = null): bool
    {
        $query = self::activos()
            ->delEmpleado($empleadoId)
            ->where('concepto_nomina_id', $conceptoId)
            ->enFecha(now());

        if ($excepto) {
            $query->where('id', '!=', $excepto);
        }

        return $query->count() === 0;
    }

    /**
     * Obtener total de devengados fijos
     */
    public static function totalDevengadosEmpleado($empleadoId, $salarioBase): float
    {
        $conceptos = self::activos()
            ->delEmpleado($empleadoId)
            ->devengados()
            ->with('concepto')
            ->get();

        $total = 0;

        foreach ($conceptos as $concepto) {
            $total += $concepto->calcularValor($salarioBase);
        }

        return $total;
    }

    /**
     * Obtener total de deducciones fijas
     */
    public static function totalDeduccionesEmpleado($empleadoId, $salarioBase): float
    {
        $conceptos = self::activos()
            ->delEmpleado($empleadoId)
            ->deducciones()
            ->with('concepto')
            ->get();

        $total = 0;

        foreach ($conceptos as $concepto) {
            $total += $concepto->calcularValor($salarioBase);
        }

        return $total;
    }
}