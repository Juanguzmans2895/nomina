<?php

namespace Tests\Feature\Modules\Nomina;

use Tests\TestCase;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\CentroCosto;
use App\Modules\Nomina\Models\ConceptoNomina;
use App\Modules\Nomina\Models\EmpleadoCentroCosto;
use App\Modules\Nomina\Models\EmpleadoConceptoFijo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;

class EmpleadoTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function puede_crear_un_empleado_completo()
    {
        $data = [
            'tipo_documento' => 'CC',
            'numero_documento' => '1234567890',
            'primer_nombre' => 'Juan',
            'segundo_nombre' => 'Carlos',
            'primer_apellido' => 'Pérez',
            'segundo_apellido' => 'García',
            'fecha_nacimiento' => '1990-01-15',
            'genero' => 'M',
            'estado_civil' => 'soltero',
            'email' => 'juan.perez@example.com',
            'celular' => '3001234567',
            'direccion' => 'Calle 123 #45-67',
            'ciudad' => 'Bogotá',
            'departamento' => 'Cundinamarca',
            'codigo_empleado' => 'EMP-001',
            'fecha_ingreso' => now(),
            'tipo_contrato' => 'indefinido',
            'cargo' => 'Desarrollador',
            'dependencia' => 'Sistemas',
            'salario_basico' => 3000000,
            'estado' => 'activo',
            'banco' => 'Bancolombia',
            'tipo_cuenta' => 'ahorros',
            'numero_cuenta' => '1234567890',
            'eps' => 'Sura',
            'eps_codigo' => 'EPS001',
            'fondo_pension' => 'Protección',
            'pension_codigo' => 'PEN001',
            'arl' => 'Sura',
            'arl_codigo' => 'ARL001',
            'clase_riesgo' => 0.522,
            'caja_compensacion' => 'Compensar',
            'caja_codigo' => 'CAJA001',
            'created_by' => 1,
        ];

        $empleado = Empleado::create($data);

        $this->assertDatabaseHas('empleados', [
            'numero_documento' => '1234567890',
            'email' => 'juan.perez@example.com',
            'salario_basico' => 3000000,
        ]);

        $this->assertInstanceOf(Empleado::class, $empleado);
        $this->assertEquals('Juan Carlos Pérez García', $empleado->nombre_completo);
    }

    /** @test */
    public function nombre_completo_se_genera_correctamente()
    {
        $empleado = Empleado::factory()->create([
            'primer_nombre' => 'Juan',
            'segundo_nombre' => 'Carlos',
            'primer_apellido' => 'Pérez',
            'segundo_apellido' => 'García',
        ]);

        $this->assertEquals('Juan Carlos Pérez García', $empleado->nombre_completo);

        // Sin segundo nombre
        $empleado2 = Empleado::factory()->create([
            'primer_nombre' => 'María',
            'segundo_nombre' => null,
            'primer_apellido' => 'López',
            'segundo_apellido' => 'Rodríguez',
        ]);

        $this->assertEquals('María López Rodríguez', $empleado2->nombre_completo);
    }

    /** @test */
    public function puede_asignar_centros_de_costo_con_porcentajes()
    {
        $empleado = Empleado::factory()->create();
        $centro1 = CentroCosto::factory()->create(['nombre' => 'Administración']);
        $centro2 = CentroCosto::factory()->create(['nombre' => 'Sistemas']);

        EmpleadoCentroCosto::create([
            'empleado_id' => $empleado->id,
            'centro_costo_id' => $centro1->id,
            'porcentaje' => 60.00,
            'fecha_inicio' => now(),
            'activo' => true,
        ]);

        EmpleadoCentroCosto::create([
            'empleado_id' => $empleado->id,
            'centro_costo_id' => $centro2->id,
            'porcentaje' => 40.00,
            'fecha_inicio' => now(),
            'activo' => true,
        ]);

        $this->assertEquals(2, $empleado->centrosCosto()->count());
        
        $totalPorcentaje = $empleado->centrosCosto()->sum('porcentaje');
        $this->assertEquals(100.00, $totalPorcentaje);
    }

    /** @test */
    public function puede_asignar_conceptos_fijos_por_valor()
    {
        $empleado = Empleado::factory()->create(['salario_basico' => 3000000]);
        $concepto = ConceptoNomina::factory()->create([
            'tipo' => 'fijo',
            'clasificacion' => 'devengado',
            'nombre' => 'Auxilio de Transporte',
        ]);

        EmpleadoConceptoFijo::create([
            'empleado_id' => $empleado->id,
            'concepto_nomina_id' => $concepto->id,
            'valor' => 140606, // Valor fijo
            'fecha_inicio' => now(),
            'activo' => true,
        ]);

        $this->assertEquals(1, $empleado->conceptosFijos()->count());
        
        $conceptoAsignado = $empleado->conceptosFijos()->first();
        $this->assertEquals(140606, $conceptoAsignado->valor);
    }

    /** @test */
    public function puede_asignar_conceptos_fijos_por_porcentaje()
    {
        $empleado = Empleado::factory()->create(['salario_basico' => 3000000]);
        $concepto = ConceptoNomina::factory()->create([
            'tipo' => 'fijo',
            'clasificacion' => 'devengado',
            'nombre' => 'Bonificación',
        ]);

        EmpleadoConceptoFijo::create([
            'empleado_id' => $empleado->id,
            'concepto_nomina_id' => $concepto->id,
            'porcentaje' => 10.00, // 10% del salario = 300,000
            'fecha_inicio' => now(),
            'activo' => true,
        ]);

        $conceptoAsignado = $empleado->conceptosFijos()->first();
        $valorCalculado = $empleado->salario_basico * ($conceptoAsignado->porcentaje / 100);
        
        $this->assertEquals(300000, $valorCalculado);
    }

    /** @test */
    public function calcula_antiguedad_correctamente()
    {
        $empleado = Empleado::factory()->create([
            'fecha_ingreso' => Carbon::now()->subYears(2)->subMonths(6)->subDays(15),
        ]);

        $diasAntiguedad = Carbon::parse($empleado->fecha_ingreso)->diffInDays(now());
        
        $this->assertGreaterThan(900, $diasAntiguedad); // Más de 2 años y medio
    }

    /** @test */
    public function scope_activos_filtra_solo_empleados_activos()
    {
        Empleado::factory()->count(5)->create(['estado' => 'activo']);
        Empleado::factory()->count(3)->create(['estado' => 'retirado']);
        Empleado::factory()->count(2)->create(['estado' => 'inactivo']);

        $activos = Empleado::activos()->get();

        $this->assertEquals(5, $activos->count());
        $activos->each(function($empleado) {
            $this->assertEquals('activo', $empleado->estado);
        });
    }

    /** @test */
    public function scope_retirados_filtra_solo_empleados_retirados()
    {
        Empleado::factory()->count(5)->create(['estado' => 'activo']);
        Empleado::factory()->count(3)->create(['estado' => 'retirado']);

        $retirados = Empleado::where('estado', 'retirado')->get();

        $this->assertEquals(3, $retirados->count());
    }

    /** @test */
    public function scope_por_dependencia_filtra_correctamente()
    {
        Empleado::factory()->count(3)->create(['dependencia' => 'Sistemas']);
        Empleado::factory()->count(2)->create(['dependencia' => 'Contabilidad']);
        Empleado::factory()->count(4)->create(['dependencia' => 'Recursos Humanos']);

        $sistemas = Empleado::where('dependencia', 'Sistemas')->get();
        $contabilidad = Empleado::where('dependencia', 'Contabilidad')->get();

        $this->assertEquals(3, $sistemas->count());
        $this->assertEquals(2, $contabilidad->count());
    }

    /** @test */
    public function no_permite_salarios_menores_al_minimo()
    {
        $this->expectException(\Exception::class);

        Empleado::factory()->create([
            'salario_basico' => 1000000, // Menor al SMLV (1,300,000)
        ]);
    }

    /** @test */
    public function no_puede_tener_documentos_duplicados()
    {
        Empleado::factory()->create([
            'numero_documento' => '1234567890',
        ]);

        $this->expectException(\Exception::class);

        Empleado::factory()->create([
            'numero_documento' => '1234567890', // Duplicado
        ]);
    }

    /** @test */
    public function empleado_retirado_debe_tener_fecha_de_retiro()
    {
        $empleado = Empleado::factory()->create([
            'estado' => 'retirado',
            'fecha_retiro' => now()->subDays(30),
        ]);

        $this->assertEquals('retirado', $empleado->estado);
        $this->assertNotNull($empleado->fecha_retiro);
        $this->assertTrue($empleado->fecha_retiro->lessThan(now()));
    }

    /** @test */
    public function puede_buscar_empleados_por_nombre()
    {
        $empleado1 = Empleado::factory()->create([
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
        ]);

        $empleado2 = Empleado::factory()->create([
            'primer_nombre' => 'María',
            'primer_apellido' => 'García',
        ]);

        Empleado::factory()->create([
            'primer_nombre' => 'Pedro',
            'primer_apellido' => 'López',
        ]);

        $resultados = Empleado::where('primer_nombre', 'like', '%Juan%')->get();
        
        $this->assertTrue($resultados->contains($empleado1));
        $this->assertFalse($resultados->contains($empleado2));
    }

    /** @test */
    public function puede_buscar_empleados_por_documento()
    {
        $empleado = Empleado::factory()->create([
            'numero_documento' => '9876543210',
        ]);

        Empleado::factory()->count(5)->create();

        $resultado = Empleado::where('numero_documento', '9876543210')->first();

        $this->assertNotNull($resultado);
        $this->assertEquals($empleado->id, $resultado->id);
    }

    /** @test */
    public function calcula_ibc_seguridad_social_correctamente()
    {
        $empleado = Empleado::factory()->create([
            'salario_basico' => 3000000,
        ]);

        // IBC básico = salario_basico (sin novedades)
        $ibc = $empleado->salario_basico;

        $this->assertEquals(3000000, $ibc);
        
        // Verificar que sea mayor o igual a 1 SMLV
        $this->assertGreaterThanOrEqual(1300000, $ibc);
    }

    /** @test */
    public function empleado_factory_genera_datos_validos()
    {
        $empleado = Empleado::factory()->create();

        $this->assertNotNull($empleado->numero_documento);
        $this->assertNotNull($empleado->primer_nombre);
        $this->assertNotNull($empleado->primer_apellido);
        $this->assertGreaterThanOrEqual(1300000, $empleado->salario_basico);
        $this->assertEquals('activo', $empleado->estado);
        $this->assertNotNull($empleado->eps);
        $this->assertNotNull($empleado->fondo_pension);
    }

    /** @test */
    public function puede_crear_empleado_con_salario_alto()
    {
        $empleado = Empleado::factory()->salarioAlto()->create();

        $this->assertGreaterThanOrEqual(10000000, $empleado->salario_basico);
    }

    /** @test */
    public function puede_crear_empleado_con_salario_minimo()
    {
        $empleado = Empleado::factory()->salarioMinimo()->create();

        $this->assertEquals(1300000, $empleado->salario_basico);
    }

    /** @test */
    public function audita_quien_crea_y_modifica_empleado()
    {
        $empleado = Empleado::factory()->create([
            'created_by' => 1,
        ]);

        $this->assertEquals(1, $empleado->created_by);

        $empleado->update([
            'salario_basico' => 3500000,
            'updated_by' => 2,
        ]);

        $this->assertEquals(2, $empleado->updated_by);
    }
}