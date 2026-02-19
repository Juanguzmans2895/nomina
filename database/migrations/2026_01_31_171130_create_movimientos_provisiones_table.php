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
        Schema::create('movimientos_provisiones', function (Blueprint $table) {
            $table->id();
            
            // Relación con empleado
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            
            // Tipo de Provisión
            $table->enum('tipo_provision', ['cesantias', 'intereses_cesantias', 'prima', 'vacaciones']);
            
            // Tipo de Movimiento
            $table->enum('tipo_movimiento', ['causacion', 'pago', 'retiro', 'ajuste']);
            
            // Valores
            $table->decimal('valor', 15, 2);
            $table->date('fecha_movimiento');
            
            // Referencia
            $table->string('numero_documento', 50)->nullable();
            $table->text('descripcion')->nullable();
            $table->text('observaciones')->nullable();
            
            // Relación con nómina (si aplica)
            $table->foreignId('nomina_id')->nullable()->constrained('nominas')->nullOnDelete();
            
            // Contabilización
            $table->boolean('contabilizado')->default(false);
            $table->string('numero_asiento', 50)->nullable();
            $table->date('fecha_contabilizacion')->nullable();
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Índices
            $table->index('empleado_id');
            $table->index('tipo_provision');
            $table->index('tipo_movimiento');
            $table->index('fecha_movimiento');
            $table->index('nomina_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_provisiones');
    }
};