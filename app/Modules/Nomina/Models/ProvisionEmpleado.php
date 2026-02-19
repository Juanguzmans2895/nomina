<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class ProvisionEmpleado extends Model
{
    use HasFactory;

    protected $table = 'provisiones_empleado';

    protected $fillable = [
        'empleado_id',
        'anio',
        'mes',
        'fecha_corte',
        'saldo_cesantias',
        'saldo_intereses_cesantias',
        'saldo_prima',
        'saldo_vacaciones',
        'causacion_cesantias_mes',
        'causacion_intereses_mes',
        'causacion_prima_mes',
        'causacion_vacaciones_mes',
        'pago_cesantias_mes',
        'pago_intereses_mes',
        'pago_prima_mes',
        'pago_vacaciones_mes',
        'salario_base',
        'dias_trabajados',
        'dias_acumulados_anio',
        'cerrado',
        'fecha_cierre',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'anio' => 'integer',
        'mes' => 'integer',
        'fecha_corte' => 'date',
        'fecha_cierre' => 'datetime',
        'saldo_cesantias' => 'decimal:2',
        'saldo_intereses_cesantias' => 'decimal:2',
        'saldo_prima' => 'decimal:2',
        'saldo_vacaciones' => 'decimal:2',
        'causacion_cesantias_mes' => 'decimal:2',
        'causacion_intereses_mes' => 'decimal:2',
        'causacion_prima_mes' => 'decimal:2',
        'causacion_vacaciones_mes' => 'decimal:2',
        'pago_cesantias_mes' => 'decimal:2',
        'pago_intereses_mes' => 'decimal:2',
        'pago_prima_mes' => 'decimal:2',
        'pago_vacaciones_mes' => 'decimal:2',
        'salario_base' => 'decimal:2',
        'dias_trabajados' => 'integer',
        'dias_acumulados_anio' => 'integer',
        'cerrado' => 'boolean',
    ];

    /**
     * Relación con empleado
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    /**
     * Relación con movimientos
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoProvision::class, 'empleado_id')
            ->whereYear('fecha_movimiento', $this->anio)
            ->whereMonth('fecha_movimiento', $this->mes);
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
     * Calcular total de saldos
     */
    public function getTotalSaldosAttribute(): float
    {
        return $this->saldo_cesantias + 
               $this->saldo_intereses_cesantias + 
               $this->saldo_prima + 
               $this->saldo_vacaciones;
    }

    /**
     * Calcular total causado en el mes
     */
    public function getTotalCausacionMesAttribute(): float
    {
        return $this->causacion_cesantias_mes + 
               $this->causacion_intereses_mes + 
               $this->causacion_prima_mes + 
               $this->causacion_vacaciones_mes;
    }

    /**
     * Calcular total pagado en el mes
     */
    public function getTotalPagoMesAttribute(): float
    {
        return $this->pago_cesantias_mes + 
               $this->pago_intereses_mes + 
               $this->pago_prima_mes + 
               $this->pago_vacaciones_mes;
    }

    /**
     * Registrar causación del mes
     */
    public function registrarCausacion(array $valores): bool
    {
        $this->causacion_cesantias_mes = $valores['cesantias'] ?? 0;
        $this->causacion_intereses_mes = $valores['intereses'] ?? 0;
        $this->causacion_prima_mes = $valores['prima'] ?? 0;
        $this->causacion_vacaciones_mes = $valores['vacaciones'] ?? 0;
        
        // Actualizar saldos
        $this->saldo_cesantias += $this->causacion_cesantias_mes;
        $this->saldo_intereses_cesantias += $this->causacion_intereses_mes;
        $this->saldo_prima += $this->causacion_prima_mes;
        $this->saldo_vacaciones += $this->causacion_vacaciones_mes;
        
        return $this->save();
    }

    /**
     * Registrar pago
     */
    public function registrarPago(string $tipoProvision, float $valor): bool
    {
        switch ($tipoProvision) {
            case 'cesantias':
                $this->pago_cesantias_mes += $valor;
                $this->saldo_cesantias -= $valor;
                break;
            case 'intereses_cesantias':
                $this->pago_intereses_mes += $valor;
                $this->saldo_intereses_cesantias -= $valor;
                break;
            case 'prima':
                $this->pago_prima_mes += $valor;
                $this->saldo_prima -= $valor;
                break;
            case 'vacaciones':
                $this->pago_vacaciones_mes += $valor;
                $this->saldo_vacaciones -= $valor;
                break;
        }
        
        return $this->save();
    }

    /**
     * Cerrar período
     */
    public function cerrar(User $usuario): bool
    {
        $this->cerrado = true;
        $this->fecha_cierre = now();
        $this->updated_by = $usuario->id;
        return $this->save();
    }

    /**
     * Scopes
     */
    public function scopePorAnio($query, int $anio)
    {
        return $query->where('anio', $anio);
    }

    public function scopePorMes($query, int $mes)
    {
        return $query->where('mes', $mes);
    }

    public function scopePorPeriodo($query, int $anio, int $mes)
    {
        return $query->where('anio', $anio)->where('mes', $mes);
    }

    public function scopeAbiertos($query)
    {
        return $query->where('cerrado', false);
    }

    public function scopePorEmpleado($query, int $empleadoId)
    {
        return $query->where('empleado_id', $empleadoId);
    }
}