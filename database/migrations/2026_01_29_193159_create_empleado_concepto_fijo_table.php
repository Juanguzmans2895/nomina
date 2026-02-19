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
        Schema::create('empleado_concepto_fijo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->foreignId('concepto_nomina_id')->constrained('conceptos_nomina')->cascadeOnDelete();
            
            // Valor del concepto
            $table->decimal('valor', 15, 2)->nullable(); // Valor fijo en pesos
            $table->decimal('porcentaje', 5, 2)->nullable(); // O porcentaje sobre salario
            $table->integer('cantidad')->nullable(); // Para conceptos por cantidad (ej: horas)
            
            // Vigencia
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            
            // Observaciones
            $table->text('observaciones')->nullable();
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Índices
            $table->index(['empleado_id', 'activo']);
            $table->index(['concepto_nomina_id', 'activo']);
            $table->index('fecha_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleado_concepto_fijo');
    }
};