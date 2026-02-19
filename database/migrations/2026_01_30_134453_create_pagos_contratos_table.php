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
        Schema::create('pagos_contratos', function (Blueprint $table) {
            $table->id();
            
            // Relación con el contrato
            $table->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            
            // Información del Pago
            $table->string('numero_pago', 50);
            $table->integer('numero_cuota')->nullable();
            $table->date('fecha_pago');
            $table->date('periodo_inicio')->nullable();
            $table->date('periodo_fin')->nullable();
            
            // Valores
            $table->decimal('valor_bruto', 15, 2);
            $table->decimal('retencion_fuente', 15, 2)->default(0);
            $table->decimal('retencion_ica', 15, 2)->default(0);
            $table->decimal('estampilla', 15, 2)->default(0);
            $table->decimal('otras_deducciones', 15, 2)->default(0);
            $table->decimal('valor_neto', 15, 2);
            
            // Información del Acta o Informe
            $table->string('numero_acta', 50)->nullable();
            $table->date('fecha_acta')->nullable();
            $table->text('descripcion_actividades')->nullable();
            $table->integer('porcentaje_avance')->nullable();
            
            // Aprobación
            $table->boolean('aprobado')->default(false);
            $table->foreignId('aprobado_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_aprobacion')->nullable();
            
            // Pago Realizado
            $table->boolean('pagado')->default(false);
            $table->string('comprobante_egreso', 50)->nullable();
            $table->date('fecha_pago_real')->nullable();
            $table->string('medio_pago', 50)->nullable(); // transferencia, cheque, etc.
            
            // Contabilización
            $table->boolean('contabilizado')->default(false);
            $table->string('numero_asiento', 50)->nullable();
            $table->date('fecha_contabilizacion')->nullable();
            
            // Documentos de Soporte
            $table->boolean('adjunta_informe')->default(false);
            $table->boolean('adjunta_factura')->default(false);
            $table->boolean('adjunta_planilla_ss')->default(false);
            $table->json('documentos_adjuntos')->nullable();
            
            // Observaciones
            $table->text('observaciones')->nullable();
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('contrato_id');
            $table->index('numero_pago');
            $table->index('fecha_pago');
            $table->index('aprobado');
            $table->index('pagado');
            $table->unique(['contrato_id', 'numero_pago']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_contratos');
    }
};