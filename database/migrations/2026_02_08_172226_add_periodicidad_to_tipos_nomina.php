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
        Schema::table('tipos_nomina', function (Blueprint $table) {
            // Agregar campo periodicidad si no existe
            if (!Schema::hasColumn('tipos_nomina', 'periodicidad')) {
                $table->enum('periodicidad', ['quincenal', 'mensual', 'semestral', 'anual', 'variable'])
                    ->default('mensual')
                    ->after('codigo');
            }
            
            // Agregar campo descripcion si no existe
            if (!Schema::hasColumn('tipos_nomina', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('nombre');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipos_nomina', function (Blueprint $table) {
            if (Schema::hasColumn('tipos_nomina', 'periodicidad')) {
                $table->dropColumn('periodicidad');
            }
            
            if (Schema::hasColumn('tipos_nomina', 'descripcion')) {
                $table->dropColumn('descripcion');
            }
        });
    }
};