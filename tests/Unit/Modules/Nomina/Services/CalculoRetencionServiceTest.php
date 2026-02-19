<?php

namespace Tests\Unit\Modules\Nomina\Services;

use Tests\TestCase;
use App\Modules\Nomina\Services\Calculo\CalculoRetencionService;
use Illuminate\Foundation\Testing\RefreshDatabase;


class CalculoRetencionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CalculoRetencionService();
    }

    /** @test */
    public function calcula_retencion_segun_tabla_uvt()
    {
        $ingreso = 5000000;
        $resultado = $this->service->calcular($ingreso);

        $this->assertArrayHasKey('retencion', $resultado);
        $this->assertTrue($resultado['retencion'] >= 0);
    }

    /** @test */
    public function aplica_deducciones_salud_pension()
    {
        $ingreso = 5000000;
        $resultado = $this->service->calcular($ingreso, [
            'incluir_salud' => true,
            'incluir_pension' => true,
        ]);

        $deduccionSalud = $ingreso * 0.04;
        $deduccionPension = $ingreso * 0.04;

        $this->assertEquals($deduccionSalud, $resultado['deducciones']['detalle']['salud']);
        $this->assertEquals($deduccionPension, $resultado['deducciones']['detalle']['pension']);
    }

    /** @test */
    public function aplica_deduccion_por_dependientes()
    {
        $uvt = 47065;
        $ingreso = 5000000;
        
        $resultado = $this->service->calcular($ingreso, [
            'numero_dependientes' => 2,
        ]);

        $deduccionEsperada = 32 * $uvt * 2; // 32 UVT por dependiente
        $this->assertEquals($deduccionEsperada, $resultado['deducciones']['detalle']['dependientes']);
    }

    /** @test */
    public function exime_retención_salarios_menores_95_uvt()
    {
        $uvt = 47065;
        $ingresoExento = 90 * $uvt; // Menor a 95 UVT

        $resultado = $this->service->calcular($ingresoExento);

        $this->assertTrue($resultado['exento']);
        $this->assertEquals(0, $resultado['retencion']);
    }

    /** @test */
    public function calcula_porcentaje_efectivo()
    {
        $ingreso = 5000000;
        $resultado = $this->service->calcular($ingreso);

        $porcentajeEsperado = ($resultado['retencion'] / $ingreso) * 100;
        
        $this->assertEquals($porcentajeEsperado, $resultado['porcentaje_efectivo']);
    }
}