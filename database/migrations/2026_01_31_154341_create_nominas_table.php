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
        Schema::create('nominas', function (Blueprint $table) {
            $table->id();
            
            // Información Básica
            $table->string('numero_nomina', 50)->unique();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            
            // Tipo y Período
            $table->foreignId('tipo_nomina_id')->constrained('tipos_nomina')->cascadeOnDelete();
            $table->foreignId('periodo_nomina_id')->constrained('periodos_nomina')->cascadeOnDelete();
            
            // Fechas
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->date('fecha_pago')->nullable();
            $table->date('fecha_pago_real')->nullable();
            
            // Valores Totales
            $table->decimal('total_devengado', 15, 2)->default(0);
            $table->decimal('total_deducciones', 15, 2)->default(0);
            $table->decimal('total_neto', 15, 2)->default(0);
            
            // Seguridad Social - Empleado
            $table->decimal('total_salud_empleado', 15, 2)->default(0);
            $table->decimal('total_pension_empleado', 15, 2)->default(0);
            $table->decimal('total_fsp_empleado', 15, 2)->default(0);
            
            // Seguridad Social - Empleador
            $table->decimal('total_salud_empleador', 15, 2)->default(0);
            $table->decimal('total_pension_empleador', 15, 2)->default(0);
            $table->decimal('total_arl_empleador', 15, 2)->default(0);
            
            // Parafiscales
            $table->decimal('total_sena', 15, 2)->default(0);
            $table->decimal('total_icbf', 15, 2)->default(0);
            $table->decimal('total_caja', 15, 2)->default(0);
            
            // Provisiones
            $table->decimal('total_cesantias', 15, 2)->default(0);
            $table->decimal('total_intereses_cesantias', 15, 2)->default(0);
            $table->decimal('total_prima', 15, 2)->default(0);
            $table->decimal('total_vacaciones', 15, 2)->default(0);
            
            // Otros
            $table->decimal('total_retencion_fuente', 15, 2)->default(0);
            $table->integer('numero_empleados')->default(0);
            
            // Estado y Flujo
            $table->enum('estado', [
                'borrador',
                'prenomina',
                'aprobada',
                'causada',
                'contabilizada',
                'pagada',
                'anulada'
            ])->default('borrador');
            
            // Validación Presupuestal
            $table->boolean('validacion_presupuestal')->default(false);
            $table->decimal('valor_presupuesto_requerido', 15, 2)->nullable();
            $table->decimal('valor_presupuesto_disponible', 15, 2)->nullable();
            $table->foreignId('validado_presupuesto_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_validacion_presupuesto')->nullable();
            $table->text('observaciones_presupuesto')->nullable();
            
            // Aprobación
            $table->foreignId('aprobado_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->text('observaciones_aprobacion')->nullable();
            
            // Causación
            $table->foreignId('causado_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_causacion')->nullable();
            
            // Contabilización
            $table->boolean('contabilizado')->default(false);
            $table->string('numero_asiento', 50)->nullable();
            $table->foreignId('contabilizado_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_contabilizacion')->nullable();
            
            // Pago
            $table->boolean('pagado')->default(false);
            $table->string('numero_comprobante_pago', 50)->nullable();
            $table->foreignId('pagado_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_pago_efectivo')->nullable();
            
            // Cierre
            $table->boolean('cerrado')->default(false);
            $table->foreignId('cerrado_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_cierre')->nullable();
            
            // Observaciones
            $table->text('observaciones')->nullable();
            $table->text('observaciones_anulacion')->nullable();
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('numero_nomina');
            $table->index('tipo_nomina_id');
            $table->index('periodo_nomina_id');
            $table->index('estado');
            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index('fecha_pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nominas');
    }
};