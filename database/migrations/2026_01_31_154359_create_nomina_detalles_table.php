<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nomina_detalles', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('nomina_id')->constrained('nominas')->cascadeOnDelete();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            
            // Datos Base del Empleado (en el momento de la liquidación)
            $table->decimal('salario_basico', 15, 2);
            $table->integer('dias_trabajados')->default(30);
            $table->integer('dias_incapacidad')->default(0);
            $table->integer('dias_licencia')->default(0);
            $table->integer('dias_suspension')->default(0);
            
            // Ingresos
            $table->decimal('total_devengado', 15, 2)->default(0);
            $table->decimal('auxilio_transporte', 15, 2)->default(0);
            $table->decimal('horas_extras', 15, 2)->default(0);
            $table->decimal('recargos', 15, 2)->default(0);
            $table->decimal('comisiones', 15, 2)->default(0);
            $table->decimal('bonificaciones', 15, 2)->default(0);
            $table->decimal('otros_ingresos', 15, 2)->default(0);
            
            // Base de Cotización
            $table->decimal('base_seguridad_social', 15, 2)->default(0);
            $table->decimal('base_parafiscales', 15, 2)->default(0);
            
            // Seguridad Social - Empleado
            $table->decimal('aporte_salud_empleado', 15, 2)->default(0);
            $table->decimal('aporte_pension_empleado', 15, 2)->default(0);
            $table->decimal('fondo_solidaridad_empleado', 15, 2)->default(0);
            
            // Seguridad Social - Empleador
            $table->decimal('aporte_salud_empleador', 15, 2)->default(0);
            $table->decimal('aporte_pension_empleador', 15, 2)->default(0);
            $table->decimal('aporte_arl_empleador', 15, 2)->default(0);
            
            // Parafiscales
            $table->decimal('aporte_sena', 15, 2)->default(0);
            $table->decimal('aporte_icbf', 15, 2)->default(0);
            $table->decimal('aporte_caja', 15, 2)->default(0);
            
            // Provisiones
            $table->decimal('provision_cesantias', 15, 2)->default(0);
            $table->decimal('provision_intereses_cesantias', 15, 2)->default(0);
            $table->decimal('provision_prima', 15, 2)->default(0);
            $table->decimal('provision_vacaciones', 15, 2)->default(0);
            
            // Deducciones
            $table->decimal('total_deducciones', 15, 2)->default(0);
            $table->decimal('retencion_fuente', 15, 2)->default(0);
            $table->decimal('prestamos', 15, 2)->default(0);
            $table->decimal('embargos', 15, 2)->default(0);
            $table->decimal('otros_descuentos', 15, 2)->default(0);
            
            // Totales
            $table->decimal('total_neto', 15, 2)->default(0);
            $table->decimal('costo_total_empleador', 15, 2)->default(0);
            
            // Distribución por Centros de Costo (JSON)
            $table->json('distribucion_centros_costo')->nullable();
            
            // Observaciones
            $table->text('observaciones')->nullable();
            
            // Auditoría
            $table->timestamps();
            
            // Índices
            $table->index('nomina_id');
            $table->index('empleado_id');
            $table->unique(['nomina_id', 'empleado_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomina_detalles');
    }
};