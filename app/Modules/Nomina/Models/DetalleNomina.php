<?php

namespace App\Modules\Nomina\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleNomina extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'detalles_nomina';

    protected $fillable = [
        'nomina_id',
        'empleado_id',
        'salario_basico',
        'dias_trabajados',
        'dias_incapacidad',
        'dias_vacaciones',
        'dias_licencia',
        
        // Devengados
        'total_devengado',
        'horas_extras_diurnas',
        'horas_extras_nocturnas',
        'horas_extras_dominicales',
        'recargo_nocturno',
        'recargo_dominical',
        'comisiones',
        'bonificaciones',
        'auxilio_transporte',
        'otros_devengados',
        
        // Deducciones
        'total_deducciones',
        'salud_empleado',
        'pension_empleado',
        'fondo_solidaridad',
        'retencion_fuente',
        'sindicato',
        'creditos',
        'embargos',
        'otras_deducciones',
        
        // Seguridad Social Empleador
        'salud_empleador',
        'pension_empleador',
        'arl_empleador',
        'caja_compensacion',
        'icbf',
        'sena',
        
        // Provisiones
        'cesantias',
        'intereses_cesantias',
        'prima_servicios',
        'vacaciones',
        
        // Totales
        'total_neto',
        'costo_total_empleador',
        
        // Observaciones
        'notas',
        'estado',
    ];

    protected $casts = [
        'salario_basico' => 'decimal:2',
        'dias_trabajados' => 'integer',
        'dias_incapacidad' => 'integer',
        'dias_vacaciones' => 'integer',
        'dias_licencia' => 'integer',
        
        'total_devengado' => 'decimal:2',
        'horas_extras_diurnas' => 'decimal:2',
        'horas_extras_nocturnas' => 'decimal:2',
        'horas_extras_dominicales' => 'decimal:2',
        'recargo_nocturno' => 'decimal:2',
        'recargo_dominical' => 'decimal:2',
        'comisiones' => 'decimal:2',
        'bonificaciones' => 'decimal:2',
        'auxilio_transporte' => 'decimal:2',
        'otros_devengados' => 'decimal:2',
        
        'total_deducciones' => 'decimal:2',
        'salud_empleado' => 'decimal:2',
        'pension_empleado' => 'decimal:2',
        'fondo_solidaridad' => 'decimal:2',
        'retencion_fuente' => 'decimal:2',
        'sindicato' => 'decimal:2',
        'creditos' => 'decimal:2',
        'embargos' => 'decimal:2',
        'otras_deducciones' => 'decimal:2',
        
        'salud_empleador' => 'decimal:2',
        'pension_empleador' => 'decimal:2',
        'arl_empleador' => 'decimal:2',
        'caja_compensacion' => 'decimal:2',
        'icbf' => 'decimal:2',
        'sena' => 'decimal:2',
        
        'cesantias' => 'decimal:2',
        'intereses_cesantias' => 'decimal:2',
        'prima_servicios' => 'decimal:2',
        'vacaciones' => 'decimal:2',
        
        'total_neto' => 'decimal:2',
        'costo_total_empleador' => 'decimal:2',
    ];

    /**
     * Relación con Nómina
     */
    public function nomina()
    {
        return $this->belongsTo(Nomina::class, 'nomina_id');
    }

    /**
     * Relación con Empleado
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    /**
     * Obtener todos los devengados
     */
    public function getDevengadosAttribute()
    {
        return [
            'salario_basico' => $this->salario_basico,
            'horas_extras_diurnas' => $this->horas_extras_diurnas ?? 0,
            'horas_extras_nocturnas' => $this->horas_extras_nocturnas ?? 0,
            'horas_extras_dominicales' => $this->horas_extras_dominicales ?? 0,
            'recargo_nocturno' => $this->recargo_nocturno ?? 0,
            'recargo_dominical' => $this->recargo_dominical ?? 0,
            'comisiones' => $this->comisiones ?? 0,
            'bonificaciones' => $this->bonificaciones ?? 0,
            'auxilio_transporte' => $this->auxilio_transporte ?? 0,
            'otros_devengados' => $this->otros_devengados ?? 0,
        ];
    }

    /**
     * Obtener todas las deducciones
     */
    public function getDeduccionesAttribute()
    {
        return [
            'salud_empleado' => $this->salud_empleado ?? 0,
            'pension_empleado' => $this->pension_empleado ?? 0,
            'fondo_solidaridad' => $this->fondo_solidaridad ?? 0,
            'retencion_fuente' => $this->retencion_fuente ?? 0,
            'sindicato' => $this->sindicato ?? 0,
            'creditos' => $this->creditos ?? 0,
            'embargos' => $this->embargos ?? 0,
            'otras_deducciones' => $this->otras_deducciones ?? 0,
        ];
    }

    /**
     * Obtener aportes del empleador
     */
    public function getAportesEmpleadorAttribute()
    {
        return [
            'salud_empleador' => $this->salud_empleador ?? 0,
            'pension_empleador' => $this->pension_empleador ?? 0,
            'arl_empleador' => $this->arl_empleador ?? 0,
            'caja_compensacion' => $this->caja_compensacion ?? 0,
            'icbf' => $this->icbf ?? 0,
            'sena' => $this->sena ?? 0,
        ];
    }

    /**
     * Obtener provisiones
     */
    public function getProvisionesAttribute()
    {
        return [
            'cesantias' => $this->cesantias ?? 0,
            'intereses_cesantias' => $this->intereses_cesantias ?? 0,
            'prima_servicios' => $this->prima_servicios ?? 0,
            'vacaciones' => $this->vacaciones ?? 0,
        ];
    }

    /**
     * Scope para filtrar por nómina
     */
    public function scopePorNomina($query, $nominaId)
    {
        return $query->where('nomina_id', $nominaId);
    }

    /**
     * Scope para filtrar por empleado
     */
    public function scopePorEmpleado($query, $empleadoId)
    {
        return $query->where('empleado_id', $empleadoId);
    }

    /**
     * Calcular salario proporcional por días trabajados
     */
    public function calcularSalarioProporcional()
    {
        $diasMes = 30; // Para nómina mensual
        $diasTrabajados = $this->dias_trabajados ?? $diasMes;
        
        return ($this->salario_basico / $diasMes) * $diasTrabajados;
    }

    /**
     * Calcular total de horas extras
     */
    public function getTotalHorasExtrasAttribute()
    {
        return ($this->horas_extras_diurnas ?? 0) +
               ($this->horas_extras_nocturnas ?? 0) +
               ($this->horas_extras_dominicales ?? 0);
    }

    /**
     * Calcular total de recargos
     */
    public function getTotalRecargosAttribute()
    {
        return ($this->recargo_nocturno ?? 0) + ($this->recargo_dominical ?? 0);
    }

    /**
     * Calcular total seguridad social empleado
     */
    public function getTotalSeguridadSocialEmpleadoAttribute()
    {
        return ($this->salud_empleado ?? 0) + 
               ($this->pension_empleado ?? 0) + 
               ($this->fondo_solidaridad ?? 0);
    }

    /**
     * Calcular total seguridad social empleador
     */
    public function getTotalSeguridadSocialEmpleadorAttribute()
    {
        return ($this->salud_empleador ?? 0) + 
               ($this->pension_empleador ?? 0) + 
               ($this->arl_empleador ?? 0);
    }

    /**
     * Calcular total parafiscales
     */
    public function getTotalParafiscalesAttribute()
    {
        return ($this->caja_compensacion ?? 0) + 
               ($this->icbf ?? 0) + 
               ($this->sena ?? 0);
    }

    /**
     * Calcular total provisiones
     */
    public function getTotalProvisionesAttribute()
    {
        return ($this->cesantias ?? 0) + 
               ($this->intereses_cesantias ?? 0) + 
               ($this->prima_servicios ?? 0) + 
               ($this->vacaciones ?? 0);
    }
}