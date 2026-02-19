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
        Schema::create('detalles_nomina', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('nomina_id')->constrained('nominas')->onDelete('cascade');
            $table->foreignId('empleado_id')->constrained('empleados')->onDelete('cascade');
            
            // Salario y días
            $table->decimal('salario_basico', 15, 2)->default(0);
            $table->integer('dias_trabajados')->default(30);
            $table->integer('dias_incapacidad')->default(0);
            $table->integer('dias_vacaciones')->default(0);
            $table->integer('dias_licencia')->default(0);
            
            // DEVENGADOS
            $table->decimal('total_devengado', 15, 2)->default(0);
            $table->decimal('horas_extras_diurnas', 15, 2)->default(0)->nullable();
            $table->decimal('horas_extras_nocturnas', 15, 2)->default(0)->nullable();
            $table->decimal('horas_extras_dominicales', 15, 2)->default(0)->nullable();
            $table->decimal('recargo_nocturno', 15, 2)->default(0)->nullable();
            $table->decimal('recargo_dominical', 15, 2)->default(0)->nullable();
            $table->decimal('comisiones', 15, 2)->default(0)->nullable();
            $table->decimal('bonificaciones', 15, 2)->default(0)->nullable();
            $table->decimal('auxilio_transporte', 15, 2)->default(0)->nullable();
            $table->decimal('otros_devengados', 15, 2)->default(0)->nullable();
            
            // DEDUCCIONES
            $table->decimal('total_deducciones', 15, 2)->default(0);
            $table->decimal('salud_empleado', 15, 2)->default(0)->nullable();
            $table->decimal('pension_empleado', 15, 2)->default(0)->nullable();
            $table->decimal('fondo_solidaridad', 15, 2)->default(0)->nullable();
            $table->decimal('retencion_fuente', 15, 2)->default(0)->nullable();
            $table->decimal('sindicato', 15, 2)->default(0)->nullable();
            $table->decimal('creditos', 15, 2)->default(0)->nullable();
            $table->decimal('embargos', 15, 2)->default(0)->nullable();
            $table->decimal('otras_deducciones', 15, 2)->default(0)->nullable();
            
            // SEGURIDAD SOCIAL EMPLEADOR
            $table->decimal('salud_empleador', 15, 2)->default(0)->nullable();
            $table->decimal('pension_empleador', 15, 2)->default(0)->nullable();
            $table->decimal('arl_empleador', 15, 2)->default(0)->nullable();
            $table->decimal('caja_compensacion', 15, 2)->default(0)->nullable();
            $table->decimal('icbf', 15, 2)->default(0)->nullable();
            $table->decimal('sena', 15, 2)->default(0)->nullable();
            
            // PROVISIONES
            $table->decimal('cesantias', 15, 2)->default(0)->nullable();
            $table->decimal('intereses_cesantias', 15, 2)->default(0)->nullable();
            $table->decimal('prima_servicios', 15, 2)->default(0)->nullable();
            $table->decimal('vacaciones', 15, 2)->default(0)->nullable();
            
            // TOTALES
            $table->decimal('total_neto', 15, 2)->default(0);
            $table->decimal('costo_total_empleador', 15, 2)->default(0)->nullable();
            
            // Observaciones
            $table->text('notas')->nullable();
            $table->string('estado', 50)->default('activo');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('nomina_id');
            $table->index('empleado_id');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_nomina');
    }
};