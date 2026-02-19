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
        Schema::create('nomina_conceptos', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('nomina_detalle_id')->constrained('nomina_detalles')->cascadeOnDelete();
            $table->foreignId('concepto_nomina_id')->constrained('conceptos_nomina')->cascadeOnDelete();
            $table->foreignId('novedad_nomina_id')->nullable()->constrained('novedades_nomina')->nullOnDelete();
            
            // Datos del Concepto
            $table->string('codigo_concepto', 50);
            $table->string('nombre_concepto', 200);
            $table->enum('clasificacion', ['devengado', 'deducido', 'no_imputable']);
            
            // Cálculo
            $table->decimal('cantidad', 10, 2)->nullable();
            $table->decimal('valor_unitario', 15, 2)->nullable();
            $table->decimal('porcentaje', 5, 2)->nullable();
            $table->decimal('valor', 15, 2);
            
            // Información Adicional
            $table->text('formula_calculo')->nullable();
            $table->text('observaciones')->nullable();
            
            // Auditoría
            $table->timestamps();
            
            // Índices
            $table->index('nomina_detalle_id');
            $table->index('concepto_nomina_id');
            $table->index('clasificacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomina_conceptos');
    }
};