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
        Schema::create('detalles_asientos_nomina', function (Blueprint $table) {
            $table->id();
            
            // Relación con asiento
            $table->foreignId('asiento_id')->constrained('asientos_contables_nomina')->cascadeOnDelete();
            
            // Cuenta Contable
            $table->foreignId('cuenta_contable_id')->constrained('cuentas_contables')->cascadeOnDelete();
            $table->string('codigo_cuenta', 50);
            $table->string('nombre_cuenta', 200);
            
            // Tercero (Empleado)
            $table->foreignId('empleado_id')->nullable()->constrained('empleados')->nullOnDelete();
            $table->string('documento_tercero', 20)->nullable();
            $table->string('nombre_tercero', 200)->nullable();
            
            // Centro de Costo
            $table->foreignId('centro_costo_id')->nullable()->constrained('centros_costo')->nullOnDelete();
            $table->string('codigo_centro_costo', 50)->nullable();
            
            // Valores
            $table->decimal('debito', 15, 2)->default(0);
            $table->decimal('credito', 15, 2)->default(0);
            
            // Base (para análisis)
            $table->decimal('base', 15, 2)->nullable();
            $table->decimal('porcentaje', 5, 2)->nullable();
            
            // Descripción
            $table->text('descripcion')->nullable();
            
            // Orden
            $table->integer('orden')->default(0);
            
            // Auditoría
            $table->timestamps();
            
            // Índices
            $table->index('asiento_id');
            $table->index('cuenta_contable_id');
            $table->index('empleado_id');
            $table->index('centro_costo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_asientos_nomina');
    }
};