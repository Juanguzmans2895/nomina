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
        Schema::create('conceptos_nomina', function (Blueprint $table) {
            $table->id();
            
            // Información Básica
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            
            // Clasificación
            $table->enum('clasificacion', ['devengado', 'deducido', 'no_imputable'])->default('devengado');
            $table->enum('tipo', ['fijo', 'novedad', 'calculado'])->default('novedad');
            
            // Configuración de Cálculo
            $table->boolean('base_salarial')->default(false); // Si forma parte del salario base para cálculos
            $table->boolean('afecta_prestaciones')->default(false); // Si afecta prestaciones sociales
            $table->boolean('afecta_seguridad_social')->default(false); // Si aporta a seguridad social
            $table->boolean('afecta_parafiscales')->default(false); // Si aporta a parafiscales
            $table->boolean('aplica_retencion')->default(false); // Si aplica retención en la fuente
            
            // Porcentajes (si aplica)
            $table->decimal('porcentaje', 5, 2)->nullable(); // Para conceptos calculados por porcentaje
            $table->string('formula')->nullable(); // Fórmula de cálculo (si aplica)
            
            // Contabilización
            $table->foreignId('cuenta_debito_id')->nullable()->constrained('cuentas_contables')->nullOnDelete();
            $table->foreignId('cuenta_credito_id')->nullable()->constrained('cuentas_contables')->nullOnDelete();
            
            // Configuración Adicional
            $table->boolean('visible_colilla')->default(true); // Si se muestra en colilla de pago
            $table->integer('orden_colilla')->default(0); // Orden de aparición en colilla
            $table->string('agrupador', 100)->nullable(); // Para agrupar conceptos en reportes
            
            // Control
            $table->boolean('activo')->default(true);
            $table->boolean('sistema')->default(false); // Si es un concepto del sistema (no editable)
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('codigo');
            $table->index('clasificacion');
            $table->index('tipo');
            $table->index('activo');
            $table->index('sistema');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conceptos_nomina');
    }
};