<?php

namespace Tests\Unit\Modules\Nomina\Services;

use Tests\TestCase;
use App\Modules\Nomina\Services\Calculo\CalculoSeguridadSocialService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CalculoSeguridadSocialServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CalculoSeguridadSocialService();
    }

    /** @test */
    public function calcula_aporte_salud_empleado_correctamente()
    {
        $salario = 3000000;
        $resultado = $this->service->calcular($salario);

        // 4% del salario
        $esperado = $salario * 0.04;
        
        $this->assertEquals($esperado, $resultado['aporte_salud_empleado']);
    }

    /** @test */
    public function calcula_aporte_salud_empleador_correctamente()
    {
        $salario = 3000000;
        $resultado = $this->service->calcular($salario);

        // 8.5% del salario
        $esperado = $salario * 0.085;
        
        $this->assertEquals($esperado, $resultado['aporte_salud_empleador']);
    }

    /** @test */
    public function calcula_aporte_pension_empleado_correctamente()
    {
        $salario = 3000000;
        $resultado = $this->service->calcular($salario);

        // 4% del salario
        $esperado = $salario * 0.04;
        
        $this->assertEquals($esperado, $resultado['aporte_pension_empleado']);
    }

    /** @test */
    public function calcula_aporte_pension_empleador_correctamente()
    {
        $salario = 3000000;
        $resultado = $this->service->calcular($salario);

        // 12% del salario
        $esperado = $salario * 0.12;
        
        $this->assertEquals($esperado, $resultado['aporte_pension_empleador']);
    }

    /** @test */
    public function aplica_tope_salud_13_smlv()
    {
        $smlv = 1300000;
        $topeSalud = 13 * $smlv;
        $salarioAlto = 20000000; // Supera el tope

        $resultado = $this->service->calcular($salarioAlto);

        // La base no debe superar 13 SMLV
        $this->assertEquals($topeSalud, $resultado['base_salud']);
        
        // El aporte debe calcularse sobre el tope
        $aporteEsperado = $topeSalud * 0.04;
        $this->assertEquals($aporteEsperado, $resultado['aporte_salud_empleado']);
    }

    /** @test */
    public function aplica_tope_pension_25_smlv()
    {
        $smlv = 1300000;
        $topePension = 25 * $smlv;
        $salarioAlto = 40000000; // Supera el tope

        $resultado = $this->service->calcular($salarioAlto);

        // La base no debe superar 25 SMLV
        $this->assertEquals($topePension, $resultado['base_pension']);
        
        // El aporte debe calcularse sobre el tope
        $aporteEsperado = $topePension * 0.04;
        $this->assertEquals($aporteEsperado, $resultado['aporte_pension_empleado']);
    }

    /** @test */
    public function calcula_fsp_cero_para_salarios_menores_4_smlv()
    {
        $smlv = 1300000;
        $salario = 3 * $smlv; // Menor a 4 SMLV

        $resultado = $this->service->calcular($salario);

        $this->assertEquals(0, $resultado['fondo_solidaridad_empleado']);
    }

    /** @test */
    public function calcula_fsp_1_porciento_para_salarios_entre_4_16_smlv()
    {
        $smlv = 1300000;
        $salario = 5 * $smlv; // Entre 4 y 16 SMLV

        $resultado = $this->service->calcular($salario);

        $esperado = $salario * 0.01;
        $this->assertEquals($esperado, $resultado['fondo_solidaridad_empleado']);
    }

    /** @test */
    public function calcula_fsp_escalonado_correctamente()
    {
        $smlv = 1300000;
        
        // Prueba con diferentes rangos
        $casos = [
            [3 * $smlv, 0],      // < 4 SMLV
            [5 * $smlv, 0.01],   // 4-16 SMLV
            [17 * $smlv, 0.012], // 16-17 SMLV
            [18 * $smlv, 0.014], // 17-18 SMLV
            [19 * $smlv, 0.016], // 18-19 SMLV
            [20 * $smlv, 0.018], // 19-20 SMLV
            [25 * $smlv, 0.02],  // > 20 SMLV
        ];

        foreach ($casos as [$salario, $porcentaje]) {
            $resultado = $this->service->calcular($salario);
            $esperado = $salario * $porcentaje;
            
            $this->assertEquals(
                $esperado,
                $resultado['fondo_solidaridad_empleado'],
                "Falló para salario: " . number_format($salario)
            );
        }
    }

    /** @test */
    public function calcula_arl_segun_clase_riesgo()
    {
        $salario = 3000000;
        $claseRiesgo = 2.436; // Clase III

        $resultado = $this->service->calcular($salario, ['clase_riesgo' => $claseRiesgo]);

        $esperado = $salario * ($claseRiesgo / 100);
        $this->assertEquals($esperado, $resultado['aporte_arl_empleador']);
    }

    /** @test */
    public function aplica_tope_arl_28_smlv()
    {
        $smlv = 1300000;
        $topeARL = 28 * $smlv;
        $salarioAlto = 50000000;
        $claseRiesgo = 4.35;

        $resultado = $this->service->calcular($salarioAlto, ['clase_riesgo' => $claseRiesgo]);

        // La base no debe superar 28 SMLV
        $this->assertEquals($topeARL, $resultado['base_arl']);
        
        // El aporte debe calcularse sobre el tope
        $aporteEsperado = $topeARL * ($claseRiesgo / 100);
        $this->assertEquals($aporteEsperado, $resultado['aporte_arl_empleador']);
    }
}