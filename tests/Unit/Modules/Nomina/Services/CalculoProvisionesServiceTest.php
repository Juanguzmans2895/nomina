<?php

namespace Tests\Unit\Modules\Nomina\Services;

use Tests\TestCase;
use App\Modules\Nomina\Services\Calculo\CalculoSeguridadSocialService;
use App\Modules\Nomina\Services\Calculo\CalculoParafiscalesService;
use App\Modules\Nomina\Services\Calculo\CalculoRetencionService;
use App\Modules\Nomina\Services\Calculo\CalculoProvisionesService;
use App\Modules\Nomina\Services\LiquidacionService;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Models\TipoNomina;
use App\Modules\Nomina\Models\PeriodoNomina;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CalculoProvisionesServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CalculoProvisionesService();
    }

    /** @test */
    public function calcula_cesantias_8_33_porciento()
    {
        $salario = 3000000;
        $resultado = $this->service->calcularCesantias($salario);

        $esperado = $salario * 0.0833;
        $this->assertEquals($esperado, $resultado);
    }

    /** @test */
    public function calcula_intereses_cesantias_1_porciento_mensual()
    {
        $salario = 3000000;
        $resultado = $this->service->calcularInteresesCesantias($salario);

        $esperado = $salario * 0.01; // 12% anual / 12 = 1% mensual
        $this->assertEquals($esperado, $resultado);
    }

    /** @test */
    public function calcula_prima_8_33_porciento()
    {
        $salario = 3000000;
        $resultado = $this->service->calcularPrima($salario);

        $esperado = $salario * 0.0833;
        $this->assertEquals($esperado, $resultado);
    }

    /** @test */
    public function calcula_vacaciones_4_17_porciento()
    {
        $salario = 3000000;
        $resultado = $this->service->calcularVacaciones($salario);

        $esperado = $salario * 0.0417;
        $this->assertEquals($esperado, $resultado);
    }

    /** @test */
    public function suma_total_provisiones_21_83_porciento()
    {
        $salario = 3000000;
        $resultado = $this->service->calcularTodas($salario);

        $total = $resultado['cesantias'] + $resultado['intereses_cesantias'] +
                 $resultado['prima'] + $resultado['vacaciones'];

        // 8.33 + 1 + 8.33 + 4.17 = 21.83%
        $esperado = $salario * 0.2183;
        
        $this->assertEquals($esperado, $total, '', 10); // Delta de 10 por redondeos
    }

    /** @test */
    public function calcula_liquidacion_completa()
    {
        $salario = 3000000;
        $diasTrabajados = 180; // 6 meses
        
        $resultado = $this->service->calcularLiquidacion($salario, $diasTrabajados);

        $this->assertArrayHasKey('cesantias', $resultado);
        $this->assertArrayHasKey('intereses_cesantias', $resultado);
        $this->assertArrayHasKey('prima', $resultado);
        $this->assertArrayHasKey('vacaciones', $resultado);
        $this->assertArrayHasKey('total_liquidacion', $resultado);
        
        $this->assertTrue($resultado['total_liquidacion'] > 0);
    }
}