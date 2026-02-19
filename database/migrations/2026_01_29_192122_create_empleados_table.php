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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            
            // Información Personal
            $table->string('tipo_documento', 20); // CC, CE, TI, PA
            $table->string('numero_documento', 20)->unique();
            $table->string('primer_nombre', 100);
            $table->string('segundo_nombre', 100)->nullable();
            $table->string('primer_apellido', 100);
            $table->string('segundo_apellido', 100)->nullable();
            $table->date('fecha_nacimiento');
            $table->enum('sexo', ['M', 'F', 'O']);
            $table->enum('estado_civil', ['soltero', 'casado', 'union_libre', 'divorciado', 'viudo'])->default('soltero');
            
            // Información de Contacto
            $table->string('email', 150)->unique();
            $table->string('email_personal', 150)->nullable();
            $table->string('telefono_movil', 20)->nullable();
            $table->string('telefono_fijo', 20)->nullable();
            $table->text('direccion')->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('departamento', 100)->nullable();
            
            // Información Laboral
            $table->string('codigo_empleado', 50)->unique();
            $table->date('fecha_ingreso');
            $table->date('fecha_retiro')->nullable();
            $table->enum('tipo_contrato', ['indefinido', 'fijo', 'obra_labor', 'prestacion_servicios'])->default('indefinido');
            $table->string('cargo', 200);
            $table->string('dependencia', 200)->nullable();
            $table->decimal('salario_basico', 15, 2);
            $table->enum('estado', ['activo', 'inactivo', 'vacaciones', 'incapacidad', 'suspension', 'retirado'])->default('activo');
            
            // Información Bancaria
            $table->string('banco', 100)->nullable();
            $table->string('tipo_cuenta', 50)->nullable(); // ahorros, corriente
            $table->string('numero_cuenta', 50)->nullable();
            
            // Seguridad Social
            $table->string('eps', 100)->nullable();
            $table->string('eps_codigo', 20)->nullable();
            $table->string('fondo_pension', 100)->nullable();
            $table->string('pension_codigo', 20)->nullable();
            $table->string('arl', 100)->nullable();
            $table->string('arl_codigo', 20)->nullable();
            $table->decimal('clase_riesgo', 3, 3)->default(0.522); // Clase de riesgo ARL
            $table->string('caja_compensacion', 100)->nullable();
            $table->string('caja_codigo', 20)->nullable();
            
            // Información Adicional
            $table->integer('numero_hijos')->default(0);
            $table->string('nivel_educativo', 100)->nullable();
            $table->string('profesion', 200)->nullable();
            $table->text('observaciones')->nullable();
            
            // Control
            $table->boolean('aplica_auxilio_transporte')->default(true);
            $table->boolean('alto_riesgo_pension')->default(false);
            $table->boolean('exento_retencion')->default(false);
            $table->decimal('porcentaje_retencion', 5, 2)->nullable();
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('codigo_empleado');
            $table->index('numero_documento');
            $table->index('estado');
            $table->index(['primer_nombre', 'primer_apellido']);
            $table->index('fecha_ingreso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};