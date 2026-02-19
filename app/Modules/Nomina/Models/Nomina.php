<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Modules\Nomina\Enums\EstadoNomina;
use App\Models\User;

class Nomina extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'nominas';

    protected $fillable = [
        'numero_nomina',
        'nombre',
        'descripcion',
        'tipo_nomina_id',
        'periodo_nomina_id',
        'fecha_inicio',
        'fecha_fin',
        'fecha_pago',
        'fecha_pago_real',
        'total_devengado',
        'total_deducciones',
        'total_neto',
        'total_salud_empleado',
        'total_pension_empleado',
        'total_fsp_empleado',
        'total_salud_empleador',
        'total_pension_empleador',
        'total_arl_empleador',
        'total_sena',
        'total_icbf',
        'total_caja',
        'total_cesantias',
        'total_intereses_cesantias',
        'total_prima',
        'total_vacaciones',
        'total_retencion_fuente',
        'numero_empleados',
        'estado',
        'validacion_presupuestal',
        'valor_presupuesto_requerido',
        'valor_presupuesto_disponible',
        'validado_presupuesto_by',
        'fecha_validacion_presupuesto',
        'observaciones_presupuesto',
        'aprobado_by',
        'fecha_aprobacion',
        'observaciones_aprobacion',
        'causado_by',
        'fecha_causacion',
        'contabilizado',
        'numero_asiento',
        'contabilizado_by',
        'fecha_contabilizacion',
        'pagado',
        'numero_comprobante_pago',
        'pagado_by',
        'fecha_pago_efectivo',
        'cerrado',
        'cerrado_by',
        'fecha_cierre',
        'observaciones',
        'observaciones_anulacion',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_pago' => 'date',
        'fecha_pago_real' => 'date',
        'fecha_validacion_presupuesto' => 'datetime',
        'fecha_aprobacion' => 'datetime',
        'fecha_causacion' => 'datetime',
        'fecha_contabilizacion' => 'datetime',
        'fecha_pago_efectivo' => 'datetime',
        'fecha_cierre' => 'datetime',
        'total_devengado' => 'decimal:2',
        'total_deducciones' => 'decimal:2',
        'total_neto' => 'decimal:2',
        'total_salud_empleado' => 'decimal:2',
        'total_pension_empleado' => 'decimal:2',
        'total_fsp_empleado' => 'decimal:2',
        'total_salud_empleador' => 'decimal:2',
        'total_pension_empleador' => 'decimal:2',
        'total_arl_empleador' => 'decimal:2',
        'total_sena' => 'decimal:2',
        'total_icbf' => 'decimal:2',
        'total_caja' => 'decimal:2',
        'total_cesantias' => 'decimal:2',
        'total_intereses_cesantias' => 'decimal:2',
        'total_prima' => 'decimal:2',
        'total_vacaciones' => 'decimal:2',
        'total_retencion_fuente' => 'decimal:2',
        'valor_presupuesto_requerido' => 'decimal:2',
        'valor_presupuesto_disponible' => 'decimal:2',
        'numero_empleados' => 'integer',
        'estado' => EstadoNomina::class,
        'validacion_presupuestal' => 'boolean',
        'contabilizado' => 'boolean',
        'pagado' => 'boolean',
        'cerrado' => 'boolean',
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
     * Relación con tipo de nómina
     */
    public function tipoNomina(): BelongsTo
    {
        return $this->belongsTo(TipoNomina::class, 'tipo_nomina_id');
    }

    /**
     * Relación con tipo de nómina (alias)
     */
    public function tipo(): BelongsTo
    {
        return $this->tipoNomina();
    }

    /**
     * Relación con período
     */
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoNomina::class, 'periodo_nomina_id');
    }

    /**
     * Relación con detalles (empleados)
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(NominaDetalle::class, 'nomina_id');
    }

    /**
     * Relación con novedades
     */
    public function novedades(): HasMany
    {
        return $this->hasMany(NovedadNomina::class, 'nomina_id');
    }

    /**
     * Usuarios relacionados
     */
    public function validadoPresupuestoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validado_presupuesto_by');
    }

    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_by');
    }

    public function causadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causado_by');
    }

    public function contabilizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contabilizado_by');
    }

    public function pagadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pagado_by');
    }

    public function cerradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrado_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Calcular totales desde los detalles
     */
    public function calcularTotales(): void
    {
        $detalles = $this->detalles;

        $this->total_devengado = $detalles->sum('total_devengado');
        $this->total_deducciones = $detalles->sum('total_deducciones');
        $this->total_neto = $detalles->sum('total_neto');
        
        $this->total_salud_empleado = $detalles->sum('aporte_salud_empleado');
        $this->total_pension_empleado = $detalles->sum('aporte_pension_empleado');
        $this->total_fsp_empleado = $detalles->sum('fondo_solidaridad_empleado');
        
        $this->total_salud_empleador = $detalles->sum('aporte_salud_empleador');
        $this->total_pension_empleador = $detalles->sum('aporte_pension_empleador');
        $this->total_arl_empleador = $detalles->sum('aporte_arl_empleador');
        
        $this->total_sena = $detalles->sum('aporte_sena');
        $this->total_icbf = $detalles->sum('aporte_icbf');
        $this->total_caja = $detalles->sum('aporte_caja');
        
        $this->total_cesantias = $detalles->sum('provision_cesantias');
        $this->total_intereses_cesantias = $detalles->sum('provision_intereses_cesantias');
        $this->total_prima = $detalles->sum('provision_prima');
        $this->total_vacaciones = $detalles->sum('provision_vacaciones');
        
        $this->total_retencion_fuente = $detalles->sum('retencion_fuente');
        $this->numero_empleados = $detalles->count();

        $this->save();
    }

    /**
     * Validar presupuesto
     */
    public function validarPresupuesto(User $usuario): bool
    {
        $this->validacion_presupuestal = true;
        $this->validado_presupuesto_by = $usuario->id;
        $this->fecha_validacion_presupuesto = now();
        $this->valor_presupuesto_requerido = $this->costo_total_empleador;
        
        // Aquí iría la integración con el módulo de presupuesto
        // Por ahora solo registramos la validación
        
        return $this->save();
    }

    /**
     * Aprobar nómina
     */
    public function aprobar(User $usuario, ?string $observaciones = null): bool
    {
        if ($this->estado !== EstadoNomina::PRENOMINA) {
            return false;
        }

        $this->estado = EstadoNomina::APROBADA;
        $this->aprobado_by = $usuario->id;
        $this->fecha_aprobacion = now();
        $this->observaciones_aprobacion = $observaciones;

        return $this->save();
    }

    /**
     * Causar nómina
     */
    public function causar(User $usuario): bool
    {
        if ($this->estado !== EstadoNomina::APROBADA) {
            return false;
        }

        $this->estado = EstadoNomina::CAUSADA;
        $this->causado_by = $usuario->id;
        $this->fecha_causacion = now();

        return $this->save();
    }

    /**
     * Contabilizar nómina
     */
    public function contabilizar(User $usuario, string $numeroAsiento): bool
    {
        if ($this->estado !== EstadoNomina::CAUSADA) {
            return false;
        }

        $this->estado = EstadoNomina::CONTABILIZADA;
        $this->contabilizado = true;
        $this->numero_asiento = $numeroAsiento;
        $this->contabilizado_by = $usuario->id;
        $this->fecha_contabilizacion = now();

        return $this->save();
    }

    /**
     * Registrar pago
     */
    public function registrarPago(User $usuario, string $comprobante): bool
    {
        if ($this->estado !== EstadoNomina::CONTABILIZADA) {
            return false;
        }

        $this->estado = EstadoNomina::PAGADA;
        $this->pagado = true;
        $this->numero_comprobante_pago = $comprobante;
        $this->pagado_by = $usuario->id;
        $this->fecha_pago_efectivo = now();

        return $this->save();
    }

    /**
     * Anular nómina
     */
    public function anular(User $usuario, string $observaciones): bool
    {
        if ($this->estado === EstadoNomina::PAGADA) {
            return false; // No se puede anular una nómina pagada
        }

        $this->estado = EstadoNomina::ANULADA;
        $this->observaciones_anulacion = $observaciones;
        $this->updated_by = $usuario->id;

        return $this->save();
    }

    /**
     * Scopes
     */
    public function scopePorEstado($query, EstadoNomina $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopeBorradores($query)
    {
        return $query->where('estado', EstadoNomina::BORRADOR);
    }

    public function scopeAprobadas($query)
    {
        return $query->where('estado', EstadoNomina::APROBADA);
    }

    public function scopePagadas($query)
    {
        return $query->where('estado', EstadoNomina::PAGADA);
    }

    public function scopePorPeriodo($query, int $periodoId)
    {
        return $query->where('periodo_nomina_id', $periodoId);
    }

    public function scopePorTipo($query, int $tipoId)
    {
        return $query->where('tipo_nomina_id', $tipoId);
    }

    public function scopeBuscar($query, string $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('numero_nomina', 'like', "%{$termino}%")
              ->orWhere('nombre', 'like', "%{$termino}%");
        });
    }

    /**
 * AGREGAR estas relaciones al modelo Nomina
    * Ubicación: app/Modules/Nomina/Models/Nomina.php
    * 
    * NOTA: Cambiamos "detalles()" por "detallesNomina()" para evitar conflicto
    */

    /**
     * Relación con detalles de la nómina (uno a muchos)
     */
    public function detallesNomina()
    {
        return $this->hasMany(DetalleNomina::class, 'nomina_id');
    }

    /**
     * Relación con empleados de la nómina (muchos a muchos)
     */
    public function empleadosNomina()
    {
        return $this->belongsToMany(Empleado::class, 'detalles_nomina', 'nomina_id', 'empleado_id')
            ->withPivot([
                'salario_basico',
                'total_devengado',
                'total_deducciones',
                'total_neto',
                'salud_empleado',
                'pension_empleado',
                'salud_empleador',
                'pension_empleador',
                'arl_empleador'
            ])
            ->withTimestamps();
    }

    /**
     * Scope para incluir detalles con empleado
     */
    public function scopeConDetallesNomina($query)
    {
        return $query->with(['detallesNomina.empleado']);
    }

    /**
     * Obtener total de empleados en esta nómina
     */
    public function getTotalEmpleadosAttribute()
    {
        return $this->detallesNomina()->count();
    }

    /**
     * Obtener costo total del empleador
     */
    public function getCostoTotalEmpleadorAttribute()
    {
        return $this->detallesNomina()->sum('costo_total_empleador');
    }

    /**
     * Relación con empleados a través de detalles
     */
    public function empleados()
    {
        return $this->belongsToMany(Empleado::class, 'detalles_nomina', 'nomina_id', 'empleado_id')
            ->withPivot([
                'salario_basico',
                'total_devengado',
                'total_deducciones',
                'total_neto',
                'salud_empleado',
                'pension_empleado',
                'salud_empleador',
                'pension_empleador',
                'arl_empleador'
            ])
            ->withTimestamps();
    }

    /**
     * Scope para incluir detalles con empleado
     */
    public function scopeConDetalles($query)
    {
        return $query->with(['detalles.empleado']);
    }

    /**
     * Recalcular totales de la nómina
     */
    public function recalcularTotales()
    {
        $this->total_devengado = $this->detalles()->sum('total_devengado');
        $this->total_deducciones = $this->detalles()->sum('total_deducciones');
        $this->total_neto = $this->detalles()->sum('total_neto');
        $this->numero_empleados = $this->detalles()->count();
        $this->save();
        
        return $this;
    }
}