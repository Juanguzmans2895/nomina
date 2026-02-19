<?php

namespace Tests\Unit\Modules\Nomina\Services;

use Tests\TestCase;
use App\Modules\Nomina\Services\LiquidacionService;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Models\TipoNomina;
use App\Modules\Nomina\Models\PeriodoNomina;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LiquidacionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LiquidacionService();
    }

    /** @test */
    public function liquida_empleado_con_salario_basico()
    {
        $empleado = Empleado::factory()->create([
            'salario_basico' => 3000000,
            'eps' => 'Sura',
            'fondo_pension' => 'Protección',
            'arl' => 'Sura',
            'clase_riesgo' => 0.522,
        ]);

        $nomina = $this->crearNominaBase();

        $resultado = $this->service->liquidarEmpleado($nomina, $empleado);

        $this->assertArrayHasKey('total_devengado', $resultado);
        $this->assertArrayHasKey('total_deducciones', $resultado);
        $this->assertArrayHasKey('total_neto', $resultado);
        
        $this->assertTrue($resultado['total_devengado'] > 0);
        $this->assertTrue($resultado['total_neto'] > 0);
    }

    /** @test */
    public function calcula_auxilio_transporte_correctamente()
    {
        $smlv = 1300000;
        
        $empleado = Empleado::factory()->create([
            'salario_basico' => 2 * $smlv, // Devenga auxilio
        ]);

        $nomina = $this->crearNominaBase();
        $resultado = $this->service->liquidarEmpleado($nomina, $empleado);

        $auxilioTransporte = 162000; // Valor 2024
        $this->assertGreaterThan($empleado->salario_basico, $resultado['total_devengado']);
    }

    /** @test */
    public function no_calcula_auxilio_transporte_para_salarios_altos()
    {
        $smlv = 1300000;
        
        $empleado = Empleado::factory()->create([
            'salario_basico' => 3 * $smlv, // No devenga auxilio
        ]);

        $nomina = $this->crearNominaBase();
        $resultado = $this->service->liquidarEmpleado($nomina, $empleado);

        $this->assertEquals($empleado->salario_basico, $resultado['total_devengado']);
    }

    protected function crearNominaBase()
    {
        $tipo = TipoNomina::factory()->create();
        $periodo = PeriodoNomina::factory()->create();

        return Nomina::factory()->create([
            'tipo_nomina_id' => $tipo->id,
            'periodo_nomina_id' => $periodo->id,
            'estado' => 'borrador',
        ]);
    }
}