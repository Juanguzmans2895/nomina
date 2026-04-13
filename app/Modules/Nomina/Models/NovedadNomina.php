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

    // CÁLCULO AUTOMÁTICO - MEJORADO
    public function calcularValor(): void
    {
        if (!$this->empleado) $this->load('empleado');
        
        $salario = $this->empleado?->salario_basico ?? 0;
        $cantidad = $this->cantidad ?? 0;

        // ══════════════════════════════════════════════════════════
        // PRIORIDAD 1: Usar fórmula del concepto si existe
        // ══════════════════════════════════════════════════════════
        if ($this->concepto && $this->concepto->formula) {
            $this->valor_total = $this->evaluarFormula($this->concepto->formula, $salario, $cantidad);
            $this->valor_unitario = $cantidad > 0 ? round($this->valor_total / $cantidad, 2) : 0;
        }
        // ══════════════════════════════════════════════════════════
        // PRIORIDAD 2: Usar porcentaje de recargo (ej: horas extras 80%)
        // ══════════════════════════════════════════════════════════
        elseif ($this->porcentaje_recargo !== null) {
            $factor = 1 + ($this->porcentaje_recargo / 100);
            // Valor unitario = (Salario / 240 horas) * factor
            $this->valor_unitario = round(($salario / 240) * $factor, 2);
            $this->valor_total = round($this->valor_unitario * $cantidad, 2);
        }
        // ══════════════════════════════════════════════════════════
        // PRIORIDAD 3: Usar valor unitario directo (manual)
        // ══════════════════════════════════════════════════════════
        else {
            $this->valor_total = round($this->valor_unitario * $cantidad, 2);
        }
    }

    /**
     * Evaluar fórmula reemplazando variables
     * Soporta: salario_basico, salario, cantidad, ibc, dias_trabajados
     */
    private function evaluarFormula(string $formula, float $salario, int $cantidad): float
    {
        try {
            // Reemplazar variables comunes
            $formula = str_replace(
                ['salario_basico', 'salario', 'cantidad', 'ibc'],
                [$salario, $salario, $cantidad, $salario],
                $formula
            );
            
            // Reemplazar salarios por día (salario / 30) y hora (salario / 240)
            $formulaWithVars = preg_replace_callback(
                '/salario\s*\/\s*(\d+)/',
                fn($m) => ($salario / (int)$m[1]),
                $formula
            );
            
            // Limpiar espacios
            $formulaWithVars = preg_replace('/\s+/', '', $formulaWithVars);
            
            // ══════════════════════════════════════════════════════════
            // VALIDACIÓN DE SEGURIDAD: Solo permitir operadores matemáticos
            // ══════════════════════════════════════════════════════════
            if (!preg_match('/^[0-9+\-*\/().]+$/', $formulaWithVars)) {
                \Log::warning("Fórmula rechazada (caracteres no permitidos): {$formula}");
                return 0;
            }
            
            // Profundidad máxima de paréntesis (para evitar bomba de recursión)
            if (substr_count($formulaWithVars, '(') > 10) {
                \Log::warning("Fórmula rechazada (demasiados paréntesis): {$formula}");
                return 0;
            }
            
            // Ejecutar fórmula de forma segura
            $resultado = 0;
            @eval('$resultado = ' . $formulaWithVars . ';');
            
            // Validar resultado
            if (!is_numeric($resultado) || $resultado < 0) {
                \Log::warning("Fórmula resultó en valor inválido: {$formulaWithVars} = {$resultado}");
                return 0;
            }
            
            return round($resultado, 2);
            
        } catch (\Exception $e) {
            \Log::error("Error evaluando fórmula: {$formula}", ['error' => $e->getMessage()]);
            return 0;
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($nov) {
            // Cargar relación si no existe
            if (!$nov->concepto && $nov->concepto_id) {
                $nov->concepto = ConceptoNomina::find($nov->concepto_id);
            }
            
            // Cargar empleado si no existe
            if (!$nov->empleado && $nov->empleado_id) {
                $nov->empleado = Empleado::find($nov->empleado_id);
            }
            
            // Configurar valores del concepto
            if ($nov->concepto) {
                $nov->tipo_novedad = $nov->tipo_novedad ?? $nov->concepto->codigo;
                $nov->aplica_formula = ($nov->concepto->formula != null);  // ← Cambio: Verificar si tiene fórmula
                $nov->formula = $nov->concepto->formula;
                $nov->porcentaje_recargo = $nov->concepto->porcentaje_recargo ?? $nov->porcentaje_recargo;
            }
            
            // Calcular valor automáticamente
            $nov->calcularValor();
        });

        static::updating(function ($nov) {
            // Cargar relación si no existe
            if (!$nov->concepto && $nov->concepto_id) {
                $nov->concepto = ConceptoNomina::find($nov->concepto_id);
            }
            
            // Cargar empleado si no existe
            if (!$nov->empleado && $nov->empleado_id) {
                $nov->empleado = Empleado::find($nov->empleado_id);
            }
            
            // Recalcular si los datos relevantes cambian
            if ($nov->isDirty(['cantidad', 'valor_unitario', 'empleado_id', 'concepto_id', 'porcentaje_recargo'])) {
                $nov->calcularValor();
            }
        });
    }

    // MÉTODOS DE ESTADO
    public function aprobar($userId = null): bool
    {
        if ($this->estado !== 'pendiente') {
            return false;
        }

        try {
            $result = $this->update([
                'estado' => 'aprobada',
                'aprobado_by' => $userId ?? auth()->id(),
                'fecha_aprobacion' => now()
            ]);
            
            // Refresh to ensure state changes are reflected
            $this->refresh();
            
            return $result !== false;
        } catch (\Exception $e) {
            \Log::error('Error aprobando novedad: ' . $e->getMessage());
            return false;
        }
    }

    public function rechazar(string $motivo, $userId = null): bool
    {
        if ($this->estado !== 'pendiente') {
            return false;
        }

        try {
            $result = $this->update([
                'estado' => 'rechazada',
                'motivo_rechazo' => $motivo,
                'aprobado_by' => $userId ?? auth()->id()
            ]);
            
            // Refresh to ensure state changes are reflected
            $this->refresh();
            
            return $result !== false;
        } catch (\Exception $e) {
            \Log::error('Error rechazando novedad: ' . $e->getMessage());
            return false;
        }
    }
}