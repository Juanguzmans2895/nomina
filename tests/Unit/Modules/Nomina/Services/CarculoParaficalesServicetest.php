<?php

namespace Tests\Unit\Modules\Nomina\Services;

use Tests\TestCase;
use App\Modules\Nomina\Services\Calculo\CalculoParafiscalesService;
use Illuminate\Foundation\Testing\RefreshDatabase;


class CalculoParafiscalesServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CalculoParafiscalesService();
    }

    /** @test */
    public function calcula_sena_2_porciento()
    {
        $salario = 3000000;
        $resultado = $this->service->calcular($salario);

        $esperado = $salario * 0.02;
        $this->assertEquals($esperado, $resultado['aporte_sena']);
    }

    /** @test */
    public function calcula_icbf_3_porciento()
    {
        $salario = 3000000;
        $resultado = $this->service->calcular($salario);

        $esperado = $salario * 0.03;
        $this->assertEquals($esperado, $resultado['aporte_icbf']);
    }

    /** @test */
    public function calcula_caja_4_porciento()
    {
        $salario = 3000000;
        $resultado = $this->service->calcular($salario);

        $esperado = $salario * 0.04;
        $this->assertEquals($esperado, $resultado['aporte_caja']);
    }

    /** @test */
    public function suma_total_parafiscales_9_porciento()
    {
        $salario = 3000000;
        $resultado = $this->service->calcular($salario);

        $esperado = $salario * 0.09; // 2% + 3% + 4%
        $this->assertEquals($esperado, $resultado['total_parafiscales']);
    }

    /** @test */
    public function exime_parafiscales_salarios_mayores_10_smlv()
    {
        $smlv = 1300000;
        $salario = 15 * $smlv; // Mayor a 10 SMLV

        $resultado = $this->service->calcular($salario);

        $this->assertTrue($resultado['exento']);
        $this->assertEquals(0, $resultado['aporte_sena']);
        $this->assertEquals(0, $resultado['aporte_icbf']);
        $this->assertEquals(0, $resultado['aporte_caja']);
        $this->assertEquals(0, $resultado['total_parafiscales']);
    }

    /** @test */
    public function no_exime_parafiscales_salarios_menores_10_smlv()
    {
        $smlv = 1300000;
        $salario = 9 * $smlv; // Menor a 10 SMLV

        $resultado = $this->service->calcular($salario);

        $this->assertFalse($resultado['exento']);
        $this->assertTrue($resultado['total_parafiscales'] > 0);
    }
}