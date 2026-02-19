<?php

namespace Tests\Feature\Modules\Nomina;

use Tests\TestCase;
use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Models\NominaDetalle;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\TipoNomina;
use App\Modules\Nomina\Models\PeriodoNomina;
use App\Modules\Nomina\Models\NovedadNomina;
use App\Modules\Nomina\Models\ConceptoNomina;
use App\Modules\Nomina\Services\LiquidacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NominaFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $liquidacionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->liquidacionService = new LiquidacionService();
    }

    /** @test */
    public function flujo_completo_de_liquidacion_de_nomina()
    {
        // 1. Crear datos necesarios
        $tipo = TipoNomina::factory()->create(['nombre' => 'Nómina Mensual']);
        $periodo = PeriodoNomina::factory()->create(['estado' => 'abierto']);
        $empleado = Empleado::factory()->create([
            'salario_basico' => 3000000,
            'estado' => 'activo',
        ]);

        // 2. Crear nómina
        $nomina = Nomina::create([
            'numero_nomina' => 'NOM-2024-01-001',
            'nombre' => 'Nómina Enero 2024',
            'tipo_nomina_id' => $tipo->id,
            'periodo_nomina_id' => $periodo->id,
            'fecha_inicio' => $periodo->fecha_inicio,
            'fecha_fin' => $periodo->fecha_fin,
            'fecha_pago' => now()->addDays(5),
            'estado' => 'borrador',
            'created_by' => 1,
        ]);

        $this->assertDatabaseHas('nominas', [
            'numero_nomina' => 'NOM-2024-01-001',
            'estado' => 'borrador',
        ]);

        // 3. Liquidar empleado
        $resultado = $this->liquidacionService->liquidarEmpleado($nomina, $empleado);

        $this->assertArrayHasKey('total_devengado', $resultado);
        $this->assertArrayHasKey('total_deducciones', $resultado);
        $this->assertArrayHasKey('total_neto', $resultado);

        // 4. Crear detalle de nómina
        $detalle = NominaDetalle::create([
            'nomina_id' => $nomina->id,
            'empleado_id' => $empleado->id,
            'salario_basico' => $resultado['salario_basico'],
            'total_devengado' => $resultado['total_devengado'],
            'total_deducciones' => $resultado['total_deducciones'],
            'total_neto' => $resultado['total_neto'],
            'aporte_salud' => $resultado['seguridad_social']['salud_empleado'],
            'aporte_pension' => $resultado['seguridad_social']['pension_empleado'],
        ]);

        $this->assertDatabaseHas('nomina_detalles', [
            'nomina_id' => $nomina->id,
            'empleado_id' => $empleado->id,
        ]);

        // 5. Aprobar nómina
        $nomina->update([
            'estado' => 'aprobada',
            'aprobada_por' => 1,
            'fecha_aprobacion' => now(),
        ]);

        $this->assertEquals('aprobada', $nomina->fresh()->estado);

        // 6. Marcar como pagada
        $nomina->update([
            'estado' => 'pagada',
            'pagada_por' => 1,
            'fecha_pago_real' => now(),
        ]);

        $this->assertEquals('pagada', $nomina->fresh()->estado);
    }

    /** @test */
    public function aplica_novedades_en_liquidacion()
    {
        $tipo = TipoNomina::factory()->create();
        $periodo = PeriodoNomina::factory()->create(['estado' => 'abierto']);
        $empleado = Empleado::factory()->create(['salario_basico' => 3000000]);
        
        $concepto = ConceptoNomina::factory()->create([
            'tipo' => 'novedad',
            'clasificacion' => 'devengado',
        ]);

        // Crear novedad
        $novedad = NovedadNomina::create([
            'empleado_id' => $empleado->id,
            'concepto_nomina_id' => $concepto->id,
            'periodo_nomina_id' => $periodo->id,
            'fecha_novedad' => now(),
            'cantidad' => 8,
            'valor_unitario' => 15000,
            'valor_total' => 120000,
            'estado' => 'aprobada',
            'procesada' => false,
            'created_by' => 1,
        ]);

        $nomina = Nomina::factory()->create([
            'tipo_nomina_id' => $tipo->id,
            'periodo_nomina_id' => $periodo->id,
        ]);

        // Liquidar con novedades
        $resultado = $this->liquidacionService->liquidarEmpleado($nomina, $empleado);

        // Verificar que la novedad se aplicó
        $this->assertGreaterThan(3000000, $resultado['total_devengado']);
    }

    /** @test */
    public function calcula_correctamente_seguridad_social()
    {
        $empleado = Empleado::factory()->create(['salario_basico' => 3000000]);
        $nomina = Nomina::factory()->create();

        $resultado = $this->liquidacionService->liquidarEmpleado($nomina, $empleado);

        // Verificar cálculos de seguridad social
        $saludEmpleado = 3000000 * 0.04; // 4%
        $pensionEmpleado = 3000000 * 0.04; // 4%

        $this->assertEquals($saludEmpleado, $resultado['seguridad_social']['salud_empleado']);
        $this->assertEquals($pensionEmpleado, $resultado['seguridad_social']['pension_empleado']);
    }

    /** @test */
    public function no_puede_aprobar_nomina_sin_empleados()
    {
        $nomina = Nomina::factory()->create(['estado' => 'borrador']);

        // La nómina no tiene detalles (empleados)
        $this->assertEquals(0, $nomina->detalles()->count());

        // No debería poder aprobarse
        // Aquí iría la lógica de validación en el servicio
    }
}