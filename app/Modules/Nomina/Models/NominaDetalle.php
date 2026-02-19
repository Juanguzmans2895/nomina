<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NominaDetalle extends Model
{
    use HasFactory;

    protected $table = 'nomina_detalles';

    protected $fillable = [
        'nomina_id',
        'empleado_id',
        'salario_basico',
        'dias_trabajados',
        'dias_incapacidad',
        'dias_licencia',
        'dias_suspension',
        'total_devengado',
        'auxilio_transporte',
        'horas_extras',
        'recargos',
        'comisiones',
        'bonificaciones',
        'otros_ingresos',
        'base_seguridad_social',
        'base_parafiscales',
        'aporte_salud_empleado',
        'aporte_pension_empleado',
        'fondo_solidaridad_empleado',
        'aporte_salud_empleador',
        'aporte_pension_empleador',
        'aporte_arl_empleador',
        'aporte_sena',
        'aporte_icbf',
        'aporte_caja',
        'provision_cesantias',
        'provision_intereses_cesantias',
        'provision_prima',
        'provision_vacaciones',
        'total_deducciones',
        'retencion_fuente',
        'prestamos',
        'embargos',
        'otros_descuentos',
        'total_neto',
        'costo_total_empleador',
        'distribucion_centros_costo',
        'observaciones',
    ];

    protected $casts = [
        'dias_trabajados' => 'integer',
        'dias_incapacidad' => 'integer',
        'dias_licencia' => 'integer',
        'dias_suspension' => 'integer',
        'salario_basico' => 'decimal:2',
        'total_devengado' => 'decimal:2',
        'auxilio_transporte' => 'decimal:2',
        'horas_extras' => 'decimal:2',
        'recargos' => 'decimal:2',
        'comisiones' => 'decimal:2',
        'bonificaciones' => 'decimal:2',
        'otros_ingresos' => 'decimal:2',
        'base_seguridad_social' => 'decimal:2',
        'base_parafiscales' => 'decimal:2',
        'aporte_salud_empleado' => 'decimal:2',
        'aporte_pension_empleado' => 'decimal:2',
        'fondo_solidaridad_empleado' => 'decimal:2',
        'aporte_salud_empleador' => 'decimal:2',
        'aporte_pension_empleador' => 'decimal:2',
        'aporte_arl_empleador' => 'decimal:2',
        'aporte_sena' => 'decimal:2',
        'aporte_icbf' => 'decimal:2',
        'aporte_caja' => 'decimal:2',
        'provision_cesantias' => 'decimal:2',
        'provision_intereses_cesantias' => 'decimal:2',
        'provision_prima' => 'decimal:2',
        'provision_vacaciones' => 'decimal:2',
        'total_deducciones' => 'decimal:2',
        'retencion_fuente' => 'decimal:2',
        'prestamos' => 'decimal:2',
        'embargos' => 'decimal:2',
        'otros_descuentos' => 'decimal:2',
        'total_neto' => 'decimal:2',
        'costo_total_empleador' => 'decimal:2',
        'distribucion_centros_costo' => 'array',
    ];

    /**
     * Relación con nómina
     */
    public function nomina(): BelongsTo
    {
        return $this->belongsTo(Nomina::class, 'nomina_id');
    }

    /**
     * Relación con empleado
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    /**
     * Relación con conceptos aplicados
     */
    public function conceptos(): HasMany
    {
        return $this->hasMany(NominaConcepto::class, 'nomina_detalle_id');
    }

    /**
     * Calcular total neto
     */
    public function calcularTotalNeto(): float
    {
        return $this->total_devengado - $this->total_deducciones;
    }

    /**
     * Calcular costo total para el empleador
     */
    public function calcularCostoTotalEmpleador(): float
    {
        return $this->total_neto +
               $this->aporte_salud_empleador +
               $this->aporte_pension_empleador +
               $this->aporte_arl_empleador +
               $this->aporte_sena +
               $this->aporte_icbf +
               $this->aporte_caja +
               $this->provision_cesantias +
               $this->provision_intereses_cesantias +
               $this->provision_prima +
               $this->provision_vacaciones;
    }
}