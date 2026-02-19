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
        Schema::create('asientos_contables_nomina', function (Blueprint $table) {
            $table->id();
            
            // Información del Asiento
            $table->string('numero_asiento', 50)->unique();
            $table->date('fecha_asiento');
            $table->string('periodo_contable', 20);
            $table->text('descripcion');
            
            // Relación con nómina
            $table->foreignId('nomina_id')->nullable()->constrained('nominas')->nullOnDelete();
            
            // Tipo de Asiento
            $table->enum('tipo_asiento', [
                'causacion_nomina',
                'pago_nomina',
                'provision_mensual',
                'pago_provision',
                'ajuste'
            ]);
            
            // Totales
            $table->decimal('total_debito', 15, 2)->default(0);
            $table->decimal('total_credito', 15, 2)->default(0);
            $table->decimal('diferencia', 15, 2)->default(0);
            
            // Estado
            $table->enum('estado', ['borrador', 'aprobado', 'contabilizado', 'anulado'])->default('borrador');
            $table->boolean('cuadrado')->default(false);
            
            // Aprobación
            $table->foreignId('aprobado_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_aprobacion')->nullable();
            
            // Contabilización
            $table->foreignId('contabilizado_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_contabilizacion')->nullable();
            
            // Observaciones
            $table->text('observaciones')->nullable();
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('numero_asiento');
            $table->index('fecha_asiento');
            $table->index('nomina_id');
            $table->index('tipo_asiento');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asientos_contables_nomina');
    }
};