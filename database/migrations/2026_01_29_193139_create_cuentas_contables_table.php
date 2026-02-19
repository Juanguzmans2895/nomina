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
        Schema::create('cuentas_contables', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['activo', 'pasivo', 'patrimonio', 'ingreso', 'gasto', 'costo']);
            $table->enum('naturaleza', ['debito', 'credito']);
            $table->integer('nivel')->default(1);
            $table->foreignId('cuenta_padre_id')->nullable()->constrained('cuentas_contables')->nullOnDelete();
            $table->boolean('maneja_tercero')->default(false);
            $table->boolean('maneja_centro_costo')->default(false);
            $table->boolean('activa')->default(true);
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('codigo');
            $table->index('tipo');
            $table->index('activa');
            $table->index('cuenta_padre_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentas_contables');
    }
};