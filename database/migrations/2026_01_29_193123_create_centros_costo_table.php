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
        Schema::create('centros_costo', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->foreignId('padre_id')->nullable()->constrained('centros_costo')->nullOnDelete();
            $table->boolean('activo')->default(true);
            
            // Presupuesto asociado
            $table->string('codigo_presupuestal', 50)->nullable();
            $table->foreignId('rubro_presupuestal_id')->nullable()->constrained('rubros_presupuestales')->nullOnDelete();
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('codigo');
            $table->index('activo');
            $table->index('padre_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centros_costo');
    }
};