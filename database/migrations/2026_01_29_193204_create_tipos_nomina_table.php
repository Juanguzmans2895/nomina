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
        Schema::create('tipos_nomina', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            
            // Configuración
            $table->enum('tipo', ['empleados', 'contratistas', 'prima', 'vacaciones', 'bonificaciones', 'liquidacion'])->default('empleados');
            $table->boolean('requiere_seguridad_social')->default(true);
            $table->boolean('requiere_parafiscales')->default(true);
            $table->boolean('requiere_provisiones')->default(true);
            
            // Control
            $table->boolean('activo')->default(true);
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('codigo');
            $table->index('tipo');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_nomina');
    }
};