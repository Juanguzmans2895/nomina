<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use app\Models\User;
use Carbon\Carbon;

class Provision extends Model
{
    use SoftDeletes;

    protected $table = 'provisiones';

    protected $fillable = [
        'empleado_id',
        'periodo_nomina_id',
        'nomina_id',
        'tipo_provision',
        'fecha_causacion',
        'saldo_cesantias',
        'saldo_intereses',
        'saldo_prima',
        'saldo_vacaciones',
        'valor_causado_cesantias',
        'valor_causado_intereses',
        'valor_causado_prima',
        'valor_causado_vacaciones',
        'valor_pagado_cesantias',
        'valor_pagado_intereses',
        'valor_pagado_prima',
        'valor_pagado_vacaciones',
        'salario_base_calculo',
        'dias_causados',
        'observaciones',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_causacion' => 'date',
        'saldo_cesantias' => 'decimal:2',
        'saldo_intereses' => 'decimal:2',
        'saldo_prima' => 'decimal:2',
        'saldo_vacaciones' => 'decimal:2',
        'valor_causado_cesantias' => 'decimal:2',
        'valor_causado_intereses' => 'decimal:2',
        'valor_causado_prima' => 'decimal:2',
        'valor_causado_vacaciones' => 'decimal:2',
        'valor_pagado_cesantias' => 'decimal:2',
        'valor_pagado_intereses' => 'decimal:2',
        'valor_pagado_prima' => 'decimal:2',
        'valor_pagado_vacaciones' => 'decimal:2',
        'salario_base_calculo' => 'decimal:2',
        'dias_causados' => 'integer',
    ];

    /**
     * Relación con Empleado
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    /**
     * Relación con Período de Nómina
     */
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoNomina::class, 'periodo_nomina_id');
    }

    /**
     * Relación con Nómina
     */
    public function nomina(): BelongsTo
    {
        return $this->belongsTo(Nomina::class, 'nomina_id');
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
     * Scope para provisiones de un empleado
     */
    public function scopeDelEmpleado($query, $empleadoId)
    {
        return $query->where('empleado_id', $empleadoId);
    }

    /**
     * Scope para provisiones de un período
     */
    public function scopeDelPeriodo($query, $periodoId)
    {
        return $query->where('periodo_nomina_id', $periodoId);
    }

    /**
     * Scope para provisiones por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_provision', $tipo);
    }

    /**
     * Calcular total de saldos
     */
    public function getTotalSaldosAttribute(): float
    {
        return $this->saldo_cesantias 
             + $this->saldo_intereses 
             + $this->saldo_prima 
             + $this->saldo_vacaciones;
    }

    /**
     * Calcular total causado en el período
     */
    public function getTotalCausadoAttribute(): float
    {
        return $this->valor_causado_cesantias 
             + $this->valor_causado_intereses 
             + $this->valor_causado_prima 
             + $this->valor_causado_vacaciones;
    }

    /**
     * Calcular total pagado
     */
    public function getTotalPagadoAttribute(): float
    {
        return $this->valor_pagado_cesantias 
             + $this->valor_pagado_intereses 
             + $this->valor_pagado_prima 
             + $this->valor_pagado_vacaciones;
    }

    /**
     * Calcular cesantías (8.33% mensual)
     */
    public static function calcularCesantias($salarioBase, $diasCausados = 30): float
    {
        return ($salarioBase * $diasCausados) / 360;
    }

    /**
     * Calcular intereses sobre cesantías (12% anual)
     */
    public static function calcularIntereses($saldoCesantias, $diasCausados = 30): float
    {
        return ($saldoCesantias * 0.12 * $diasCausados) / 360;
    }

    /**
     * Calcular prima de servicios (8.33% mensual)
     */
    public static function calcularPrima($salarioBase, $diasCausados = 30): float
    {
        return ($salarioBase * $diasCausados) / 360;
    }

    /**
     * Calcular vacaciones (4.17% mensual)
     */
    public static function calcularVacaciones($salarioBase, $diasCausados = 30): float
    {
        return ($salarioBase * $diasCausados) / 720;
    }

    /**
     * Causar provisiones del mes
     */
    public function causarMes($salarioBase, $dias = 30): void
    {
        // Cesantías
        $this->valor_causado_cesantias = self::calcularCesantias($salarioBase, $dias);
        $this->saldo_cesantias += $this->valor_causado_cesantias;

        // Intereses sobre cesantías
        $this->valor_causado_intereses = self::calcularIntereses($this->saldo_cesantias, $dias);
        $this->saldo_intereses += $this->valor_causado_intereses;

        // Prima
        $this->valor_causado_prima = self::calcularPrima($salarioBase, $dias);
        $this->saldo_prima += $this->valor_causado_prima;

        // Vacaciones
        $this->valor_causado_vacaciones = self::calcularVacaciones($salarioBase, $dias);
        $this->saldo_vacaciones += $this->valor_causado_vacaciones;

        $this->salario_base_calculo = $salarioBase;
        $this->dias_causados = $dias;
        $this->fecha_causacion = now();
    }

    /**
     * Registrar pago de cesantías
     */
    public function pagarCesantias($valor): void
    {
        if ($valor > $this->saldo_cesantias) {
            throw new \Exception('El valor a pagar excede el saldo disponible de cesantías');
        }

        $this->valor_pagado_cesantias += $valor;
        $this->saldo_cesantias -= $valor;
    }

    /**
     * Registrar pago de intereses
     */
    public function pagarIntereses($valor): void
    {
        if ($valor > $this->saldo_intereses) {
            throw new \Exception('El valor a pagar excede el saldo disponible de intereses');
        }

        $this->valor_pagado_intereses += $valor;
        $this->saldo_intereses -= $valor;
    }

    /**
     * Registrar pago de prima
     */
    public function pagarPrima($valor): void
    {
        if ($valor > $this->saldo_prima) {
            throw new \Exception('El valor a pagar excede el saldo disponible de prima');
        }

        $this->valor_pagado_prima += $valor;
        $this->saldo_prima -= $valor;
    }

    /**
     * Registrar pago de vacaciones
     */
    public function pagarVacaciones($valor): void
    {
        if ($valor > $this->saldo_vacaciones) {
            throw new \Exception('El valor a pagar excede el saldo disponible de vacaciones');
        }

        $this->valor_pagado_vacaciones += $valor;
        $this->saldo_vacaciones -= $valor;
    }

    /**
     * Liquidar todas las provisiones (retiro)
     */
    public function liquidarTotal(): array
    {
        return [
            'cesantias' => $this->saldo_cesantias,
            'intereses' => $this->saldo_intereses,
            'prima' => $this->saldo_prima,
            'vacaciones' => $this->saldo_vacaciones,
            'total' => $this->total_saldos,
        ];
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check() && !$model->created_by) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    /**
     * Calcular provisión total del empleado
     */
    public static function calcularProvisionTotal($empleadoId): array
    {
        $provision = self::delEmpleado($empleadoId)
            ->selectRaw('
                SUM(saldo_cesantias) as total_cesantias,
                SUM(saldo_intereses) as total_intereses,
                SUM(saldo_prima) as total_prima,
                SUM(saldo_vacaciones) as total_vacaciones
            ')
            ->first();

        return [
            'cesantias' => $provision->total_cesantias ?? 0,
            'intereses' => $provision->total_intereses ?? 0,
            'prima' => $provision->total_prima ?? 0,
            'vacaciones' => $provision->total_vacaciones ?? 0,
            'total' => ($provision->total_cesantias ?? 0) 
                     + ($provision->total_intereses ?? 0)
                     + ($provision->total_prima ?? 0)
                     + ($provision->total_vacaciones ?? 0),
        ];
    }

    /**
     * Obtener porcentaje de provisión sobre salario
     */
    public static function getPorcentajeProvisionTotal(): float
    {
        // Cesantías: 8.33%
        // Intereses: 1% (aproximado)
        // Prima: 8.33%
        // Vacaciones: 4.17%
        // Total: 21.83%
        return 21.83;
    }
}