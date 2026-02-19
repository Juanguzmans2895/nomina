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
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            
            // Información del Contrato
            $table->string('numero_contrato', 50)->unique();
            $table->string('tipo_contrato', 100)->default('prestacion_servicios');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->integer('plazo_meses')->nullable();
            $table->integer('plazo_dias')->nullable();
            
            // Información del Contratista
            $table->string('tipo_documento_contratista', 20);
            $table->string('numero_documento_contratista', 20);
            $table->string('nombre_contratista', 200);
            $table->string('email_contratista', 150)->nullable();
            $table->string('telefono_contratista', 20)->nullable();
            $table->text('direccion_contratista')->nullable();
            $table->string('ciudad_contratista', 100)->nullable();
            
            // Valores del Contrato
            $table->decimal('valor_total', 15, 2);
            $table->decimal('valor_mensual', 15, 2)->nullable();
            $table->decimal('valor_pagado', 15, 2)->default(0);
            $table->decimal('saldo_pendiente', 15, 2)->default(0);
            
            // Objeto del Contrato
            $table->text('objeto');
            $table->text('obligaciones')->nullable();
            $table->text('productos_entregables')->nullable();
            
            // Supervisión
            $table->foreignId('supervisor_id')->nullable()->constrained('empleados')->nullOnDelete();
            $table->string('supervisor_nombre', 200)->nullable();
            
            // Centro de Costo
            $table->foreignId('centro_costo_id')->nullable()->constrained('centros_costo')->nullOnDelete();
            
            // Retenciones Aplicables
            $table->boolean('aplica_retencion_fuente')->default(true);
            $table->decimal('porcentaje_retencion_fuente', 5, 2)->default(10.00);
            $table->boolean('aplica_retencion_ica')->default(false);
            $table->decimal('porcentaje_retencion_ica', 5, 2)->default(0);
            $table->boolean('aplica_estampilla')->default(false);
            $table->decimal('porcentaje_estampilla', 5, 2)->default(0);
            
            // Información Bancaria del Contratista
            $table->string('banco', 100)->nullable();
            $table->string('tipo_cuenta', 50)->nullable();
            $table->string('numero_cuenta', 50)->nullable();
            
            // Seguridad Social (si aplica)
            $table->boolean('requiere_seguridad_social')->default(false);
            $table->boolean('adjunta_planilla_pago')->default(false);
            
            // Pólizas
            $table->boolean('requiere_poliza')->default(false);
            $table->string('numero_poliza', 50)->nullable();
            $table->string('aseguradora', 100)->nullable();
            $table->date('fecha_vencimiento_poliza')->nullable();
            
            // Documentos Anexos
            $table->json('documentos_adjuntos')->nullable();
            
            // Estado del Contrato
            $table->enum('estado', [
                'borrador',
                'en_tramite',
                'aprobado',
                'activo',
                'suspendido',
                'terminado',
                'liquidado',
                'anulado'
            ])->default('borrador');
            
            // Observaciones
            $table->text('observaciones')->nullable();
            $table->text('observaciones_terminacion')->nullable();
            $table->date('fecha_terminacion_real')->nullable();
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('numero_contrato');
            $table->index('numero_documento_contratista');
            $table->index('estado');
            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index('supervisor_id');
            $table->index('centro_costo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};