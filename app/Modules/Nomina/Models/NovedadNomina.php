<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NovedadNomina extends Model
{
    use SoftDeletes;

    protected $table = 'novedades_nomina';

    protected $fillable = [
        'empleado_id', 'concepto_id', 'periodo_id', 'nomina_id',
        'tipo_novedad', 'fecha', 'cantidad', 'unidad',
        'valor_unitario', 'valor_total', 'porcentaje_recargo',
        'aplica_formula', 'formula', 'estado', 'observaciones',
        'archivo_soporte', 'aprobado_by', 'fecha_aprobacion',
        'motivo_rechazo', 'created_by', 'updated_by',
    ];

    protected $appends = ['procesada'];

    protected $casts = [
        'fecha' => 'date',
        'cantidad' => 'integer',
        'valor_unitario' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'porcentaje_recargo' => 'decimal:2',
        'aplica_formula' => 'boolean',
        'fecha_aprobacion' => 'datetime',
    ];

    // RELACIONES
    public function empleado() { return $this->belongsTo(Empleado::class); }
    public function concepto() { return $this->belongsTo(ConceptoNomina::class); }
    public function periodo() { return $this->belongsTo(PeriodoNomina::class); }
    public function nomina() { return $this->belongsTo(Nomina::class); }

    // SCOPES
    public function scopePendientes($q) { return $q->where('estado', 'pendiente'); }
    public function scopeAprobadas($q) { return $q->where('estado', 'aprobada'); }
    public function scopeDelPeriodo($q, $p) { return $q->where('periodo_id', $p); }

    public function getProcesadaAttribute(): bool
    {
        return $this->estado === 'aplicada';
    }

    // CÁLCULO AUTOMÁTICO
    public function calcularValor(): void
    {
        if (!$this->empleado) $this->load('empleado');
        
        $salario = $this->empleado->salario_basico ?? 0;
        $cantidad = $this->cantidad ?? 0;

        if ($this->concepto && $this->concepto->formula) {
            $this->valor_total = $this->evaluarFormula($this->concepto->formula, $salario, $cantidad);
            $this->valor_unitario = $cantidad > 0 ? round($this->valor_total / $cantidad, 2) : 0;
        } elseif ($this->porcentaje_recargo) {
            $factor = 1 + ($this->porcentaje_recargo / 100);
            $this->valor_unitario = round(($salario / 240) * $factor, 2);
            $this->valor_total = round($this->valor_unitario * $cantidad, 2);
        } else {
            $this->valor_total = round($this->valor_unitario * $cantidad, 2);
        }
    }

    private function evaluarFormula(string $formula, float $salario, int $cantidad): float
    {
        $formula = str_replace(['salario_basico', 'salario', 'cantidad', 'ibc'], [$salario, $salario, $cantidad, $salario], $formula);
        $formula = preg_replace('/\s+/', '', $formula);
        
        if (!preg_match('/^[0-9+\-*\/().]+$/', $formula)) return 0;

        try {
            $resultado = 0;
            eval('$resultado = ' . $formula . ';');
            return round($resultado, 2);
        } catch (\Exception $e) {
            \Log::error("Error fórmula: {$formula}", ['error' => $e->getMessage()]);
            return 0;
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($nov) {
            if (!$nov->concepto && $nov->concepto_id) {
                $nov->concepto = ConceptoNomina::find($nov->concepto_id);
            }
            if ($nov->concepto) {
                $nov->tipo_novedad = $nov->tipo_novedad ?? $nov->concepto->codigo;
                $nov->aplica_formula = $nov->concepto->tipo_calculo === 'formula';
                $nov->formula = $nov->concepto->formula;
                $nov->porcentaje_recargo = $nov->concepto->porcentaje_recargo;
            }
            $nov->calcularValor();
        });

        static::updating(function ($nov) {
            if ($nov->isDirty(['cantidad', 'valor_unitario', 'empleado_id'])) {
                $nov->calcularValor();
            }
        });
    }

    // MÉTODOS DE ESTADO
    public function aprobar($userId = null): bool
    {
        if ($this->estado !== 'pendiente') return false;
        $this->update(['estado' => 'aprobada', 'aprobado_by' => $userId ?? auth()->id(), 'fecha_aprobacion' => now()]);
        return true;
    }

    public function rechazar(string $motivo, $userId = null): bool
    {
        if ($this->estado !== 'pendiente') return false;
        $this->update(['estado' => 'rechazada', 'motivo_rechazo' => $motivo, 'aprobado_by' => $userId ?? auth()->id()]);
        return true;
    }
}