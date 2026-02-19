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
        Schema::create('empleado_centro_costo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->foreignId('centro_costo_id')->constrained('centros_costo')->cascadeOnDelete();
            $table->decimal('porcentaje', 5, 2)->default(100.00); // Porcentaje de distribución
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Índices
            $table->index(['empleado_id', 'activo']);
            $table->index(['centro_costo_id', 'activo']);
            $table->unique(['empleado_id', 'centro_costo_id', 'fecha_inicio'], 'unique_empleado_centro_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleado_centro_costo');
    }
};