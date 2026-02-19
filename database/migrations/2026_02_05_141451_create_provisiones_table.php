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
        Schema::create('provisiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->onDelete('cascade');
            $table->foreignId('periodo_nomina_id')->nullable()->constrained('periodos_nomina')->onDelete('set null');
            $table->foreignId('nomina_id')->nullable()->constrained('nominas')->onDelete('set null');
            
            // Tipo de provisión
            $table->enum('tipo_provision', ['mensual', 'anual', 'retiro'])->default('mensual');
            $table->date('fecha_causacion');
            
            // Saldos acumulados
            $table->decimal('saldo_cesantias', 15, 2)->default(0);
            $table->decimal('saldo_intereses', 15, 2)->default(0);
            $table->decimal('saldo_prima', 15, 2)->default(0);
            $table->decimal('saldo_vacaciones', 15, 2)->default(0);
            
            // Valores causados en el período
            $table->decimal('valor_causado_cesantias', 15, 2)->default(0);
            $table->decimal('valor_causado_intereses', 15, 2)->default(0);
            $table->decimal('valor_causado_prima', 15, 2)->default(0);
            $table->decimal('valor_causado_vacaciones', 15, 2)->default(0);
            
            // Valores pagados
            $table->decimal('valor_pagado_cesantias', 15, 2)->default(0);
            $table->decimal('valor_pagado_intereses', 15, 2)->default(0);
            $table->decimal('valor_pagado_prima', 15, 2)->default(0);
            $table->decimal('valor_pagado_vacaciones', 15, 2)->default(0);
            
            // Datos de cálculo
            $table->decimal('salario_base_calculo', 15, 2)->default(0);
            $table->integer('dias_causados')->default(30);
            
            $table->text('observaciones')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('empleado_id');
            $table->index('periodo_nomina_id');
            $table->index('fecha_causacion');
            $table->index('tipo_provision');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provisiones');
    }
};