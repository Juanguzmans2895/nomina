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
        Schema::create('modificaciones_contratos', function (Blueprint $table) {
            $table->id();
            
            // Relación con el contrato
            $table->foreignId('contrato_id')->constrained('contratos')->cascadeOnDelete();
            
            // Tipo de Modificación
            $table->enum('tipo_modificacion', [
                'adicion',
                'prorroga',
                'suspension',
                'reinicio',
                'modificacion',
                'otro'
            ]);
            
            // Información de la Modificación
            $table->string('numero_modificacion', 50);
            $table->date('fecha_modificacion');
            $table->text('justificacion');
            
            // Valores (para adiciones)
            $table->decimal('valor_adicion', 15, 2)->nullable();
            $table->decimal('nuevo_valor_total', 15, 2)->nullable();
            
            // Plazos (para prórrogas)
            $table->integer('dias_prorroga')->nullable();
            $table->date('nueva_fecha_fin')->nullable();
            
            // Suspensión
            $table->date('fecha_suspension')->nullable();
            $table->date('fecha_reinicio')->nullable();
            $table->integer('dias_suspension')->nullable();
            
            // Otras Modificaciones
            $table->text('descripcion_modificacion')->nullable();
            
            // Documentos
            $table->json('documentos_adjuntos')->nullable();
            
            // Estado
            $table->enum('estado', ['borrador', 'aprobado', 'rechazado', 'anulado'])->default('borrador');
            $table->text('observaciones')->nullable();
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('aprobado_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('contrato_id');
            $table->index('tipo_modificacion');
            $table->index('estado');
            $table->index('fecha_modificacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modificaciones_contratos');
    }
};