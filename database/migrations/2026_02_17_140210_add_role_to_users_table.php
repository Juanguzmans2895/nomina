<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Solo agregar si no existen las columnas
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'rrhh', 'contador', 'supervisor', 'consulta'])
                      ->default('consulta')
                      ->after('email');
            }
            if (!Schema::hasColumn('users', 'activo')) {
                $table->boolean('activo')->default(true)->after('role');
            }
            if (!Schema::hasColumn('users', 'empleado_id')) {
                $table->foreignId('empleado_id')
                      ->nullable()
                      ->after('activo')
                      ->constrained('empleados')
                      ->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'ultimo_acceso')) {
                $table->timestamp('ultimo_acceso')->nullable()->after('empleado_id');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('ultimo_acceso');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('empleado_id');
            $table->dropColumn(['role', 'activo', 'ultimo_acceso', 'avatar']);
        });
    }
};