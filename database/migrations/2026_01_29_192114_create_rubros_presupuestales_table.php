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
        Schema::create('rubros_presupuestales', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->foreignId('padre_id')->nullable()->constrained('rubros_presupuestales')->nullOnDelete();
            $table->integer('nivel')->default(1);
            $table->decimal('valor_aprobado', 15, 2)->default(0);
            $table->decimal('valor_disponible', 15, 2)->default(0);
            $table->boolean('activo')->default(true);
            
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
        Schema::dropIfExists('rubros_presupuestales');
    }
};