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
        Schema::create('novedades_nomina', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->foreignId('concepto_nomina_id')->constrained('conceptos_nomina')->cascadeOnDelete();
            $table->foreignId('periodo_nomina_id')->nullable()->constrained('periodos_nomina')->nullOnDelete();
            $table->foreignId('nomina_id')->nullable()->constrained('nominas')->nullOnDelete();
            
            // Datos de la Novedad
            $table->date('fecha_novedad');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            
            // Valores
            $table->decimal('cantidad', 10, 2)->nullable(); // Para horas, días, etc.
            $table->decimal('valor_unitario', 15, 2)->nullable();
            $table->decimal('porcentaje', 5, 2)->nullable();
            $table->decimal('valor_total', 15, 2);
            
            // Información Adicional
            $table->text('descripcion')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('referencia', 100)->nullable(); // Número de acta, autorización, etc.
            
            // Estado
            $table->enum('estado', ['pendiente', 'aplicada', 'rechazada', 'anulada'])->default('pendiente');
            $table->boolean('procesada')->default(false);
            
            // Aprobación
            $table->boolean('requiere_aprobacion')->default(false);
            $table->foreignId('aprobado_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_aprobacion')->nullable();
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('empleado_id');
            $table->index('concepto_nomina_id');
            $table->index('periodo_nomina_id');
            $table->index('nomina_id');
            $table->index('fecha_novedad');
            $table->index('estado');
            $table->index('procesada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('novedades_nomina');
    }
};