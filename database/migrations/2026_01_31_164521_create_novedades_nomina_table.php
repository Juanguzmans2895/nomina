<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('novedades_nomina', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->foreignId('concepto_id')->constrained('conceptos_nomina')->cascadeOnDelete();
            $table->foreignId('periodo_id')->nullable()->constrained('periodos_nomina')->nullOnDelete();
            $table->foreignId('nomina_id')->nullable()->constrained('nominas')->nullOnDelete();
            
            // Información de la novedad
            $table->string('tipo_novedad', 50); // hora_extra, incapacidad, bonificacion, descuento, etc.
            $table->date('fecha');
            $table->integer('cantidad')->default(0); // Horas, días, unidades
            $table->string('unidad', 20)->default('unidad'); // horas, dias, unidad, porcentaje
            
            // Valores
            $table->decimal('valor_unitario', 15, 2)->default(0);
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->decimal('porcentaje_recargo', 5, 2)->nullable(); // Para horas extras
            
            // Fórmula y cálculo automático
            $table->boolean('aplica_formula')->default(false);
            $table->string('formula', 200)->nullable(); // Ej: "salario/240*1.25*cantidad"
            
            // Estado
            $table->enum('estado', ['pendiente', 'aprobada', 'aplicada', 'rechazada'])->default('pendiente');
            
            // Documentación
            $table->text('observaciones')->nullable();
            $table->string('archivo_soporte')->nullable();
            
            // Aprobación
            $table->foreignId('aprobado_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->text('motivo_rechazo')->nullable();
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('empleado_id');
            $table->index('concepto_id');
            $table->index('periodo_id');
            $table->index('nomina_id');
            $table->index('tipo_novedad');
            $table->index('estado');
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('novedades_nomina');
    }
};