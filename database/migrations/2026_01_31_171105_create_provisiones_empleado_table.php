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
        Schema::create('provisiones_empleado', function (Blueprint $table) {
            $table->id();
            
            // Relación con empleado
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            
            // Período
            $table->integer('anio');
            $table->integer('mes');
            $table->date('fecha_corte');
            
            // Saldos Acumulados
            $table->decimal('saldo_cesantias', 15, 2)->default(0);
            $table->decimal('saldo_intereses_cesantias', 15, 2)->default(0);
            $table->decimal('saldo_prima', 15, 2)->default(0);
            $table->decimal('saldo_vacaciones', 15, 2)->default(0);
            
            // Causación del Mes
            $table->decimal('causacion_cesantias_mes', 15, 2)->default(0);
            $table->decimal('causacion_intereses_mes', 15, 2)->default(0);
            $table->decimal('causacion_prima_mes', 15, 2)->default(0);
            $table->decimal('causacion_vacaciones_mes', 15, 2)->default(0);
            
            // Pagos del Mes (disminuciones)
            $table->decimal('pago_cesantias_mes', 15, 2)->default(0);
            $table->decimal('pago_intereses_mes', 15, 2)->default(0);
            $table->decimal('pago_prima_mes', 15, 2)->default(0);
            $table->decimal('pago_vacaciones_mes', 15, 2)->default(0);
            
            // Información de Cálculo
            $table->decimal('salario_base', 15, 2);
            $table->integer('dias_trabajados')->default(30);
            $table->integer('dias_acumulados_anio')->default(0);
            
            // Control
            $table->boolean('cerrado')->default(false);
            $table->timestamp('fecha_cierre')->nullable();
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Índices
            $table->index('empleado_id');
            $table->index(['anio', 'mes']);
            $table->index('fecha_corte');
            $table->unique(['empleado_id', 'anio', 'mes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provisiones_empleado');
    }
};