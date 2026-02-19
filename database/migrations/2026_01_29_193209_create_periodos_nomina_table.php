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
        Schema::create('periodos_nomina', function (Blueprint $table) {
            $table->id();
            
            // Información del período
            $table->string('nombre', 100);
            $table->string('codigo', 50)->unique()->nullable();
            $table->integer('anio');
            $table->integer('mes');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            
            // Estado
            $table->enum('estado', ['abierto', 'cerrado', 'bloqueado'])->default('abierto');
            
            // Tipo de nómina (opcional)
            $table->foreignId('tipo_nomina_id')->nullable()->constrained('tipos_nomina')->nullOnDelete();
            
            // Control
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cerrado_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_cierre')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['anio', 'mes']);
            $table->index('estado');
            $table->index('fecha_inicio');
            $table->index('fecha_fin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodos_nomina');
    }
};