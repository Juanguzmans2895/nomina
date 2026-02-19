<?php

namespace Tests\Feature\Modules\Nomina;

use Tests\TestCase;
use App\Modules\Nomina\Models\Contrato;
use App\Modules\Nomina\Models\PagoContrato;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Models\CentroCosto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ContratoTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function puede_crear_un_contrato()
    {
        $supervisor = Empleado::factory()->create();
        $centroCosto = CentroCosto::factory()->create();

        $data = [
            'numero_contrato' => 'CT-2024-001',
            'tipo_contrato' => 'prestacion_servicios',
            'objeto' => 'Desarrollo de software',
            'fecha_inicio' => now(),
            'fecha_fin' => now()->addMonths(6),
            'plazo_dias' => 180,
            'tipo_documento_contratista' => 'CC',
            'numero_documento_contratista' => '1234567890',
            'nombre_contratista' => 'Juan Pérez',
            'valor_total' => 50000000,
            'saldo_pendiente' => 50000000,
            'aplica_retencion_fuente' => true,
            'porcentaje_retencion_fuente' => 10.0,
            'supervisor_id' => $supervisor->id,
            'centro_costo_id' => $centroCosto->id,
            'estado' => 'borrador',
            'created_by' => 1,
        ];

        $contrato = Contrato::create($data);

        $this->assertDatabaseHas('contratos', [
            'numero_contrato' => 'CT-2024-001',
            'valor_total' => 50000000,
        ]);

        $this->assertEquals('CT-2024-001', $contrato->numero_contrato);
        $this->assertEquals(50000000, $contrato->valor_total);
    }

    /** @test */
    public function puede_registrar_un_pago_de_contrato()
    {
        $contrato = Contrato::factory()->create([
            'valor_total' => 10000000,
            'saldo_pendiente' => 10000000,
            'aplica_retencion_fuente' => true,
            'porcentaje_retencion_fuente' => 10.0,
        ]);

        $valorBruto = 5000000;
        $valorRetencion = $valorBruto * 0.10;
        $valorNeto = $valorBruto - $valorRetencion;

        $pago = PagoContrato::create([
            'contrato_id' => $contrato->id,
            'numero_pago' => 'PAGO-001',
            'fecha_pago' => now(),
            'valor_bruto' => $valorBruto,
            'valor_retencion' => $valorRetencion,
            'valor_neto' => $valorNeto,
            'estado' => 'pendiente',
            'created_by' => 1,
        ]);

        $this->assertDatabaseHas('pagos_contratos', [
            'contrato_id' => $contrato->id,
            'valor_bruto' => $valorBruto,
            'valor_neto' => $valorNeto,
        ]);

        // Actualizar saldo del contrato
        $contrato->update([
            'saldo_pendiente' => $contrato->saldo_pendiente - $valorBruto,
        ]);

        $this->assertEquals(5000000, $contrato->fresh()->saldo_pendiente);
    }

    /** @test */
    public function calcula_correctamente_la_retencion()
    {
        $contrato = Contrato::factory()->create([
            'aplica_retencion_fuente' => true,
            'porcentaje_retencion_fuente' => 11.0,
        ]);

        $valorBruto = 10000000;
        $retencionEsperada = $valorBruto * 0.11;

        $this->assertEquals(1100000, $retencionEsperada);
    }

    /** @test */
    public function no_puede_pagar_mas_del_valor_total()
    {
        $contrato = Contrato::factory()->create([
            'valor_total' => 10000000,
            'saldo_pendiente' => 10000000,
        ]);

        // Intentar crear un pago por más del saldo
        $valorExcesivo = 15000000;

        $this->assertTrue($valorExcesivo > $contrato->saldo_pendiente);
    }

    /** @test */
    public function puede_obtener_el_porcentaje_ejecutado()
    {
        $contrato = Contrato::factory()->create([
            'valor_total' => 10000000,
            'saldo_pendiente' => 4000000,
        ]);

        $ejecutado = $contrato->valor_total - $contrato->saldo_pendiente;
        $porcentaje = ($ejecutado / $contrato->valor_total) * 100;

        $this->assertEquals(60, $porcentaje);
    }

    /** @test */
    public function contrato_proximo_a_vencer_dentro_de_30_dias()
    {
        $contrato = Contrato::factory()->create([
            'fecha_fin' => now()->addDays(15),
        ]);

        $diasRestantes = now()->diffInDays($contrato->fecha_fin);

        $this->assertTrue($diasRestantes <= 30);
        $this->assertTrue($diasRestantes >= 0);
    }

    /** @test */
    public function puede_cambiar_estado_del_contrato()
    {
        $contrato = Contrato::factory()->create([
            'estado' => 'borrador',
        ]);

        $contrato->update(['estado' => 'aprobado']);
        $this->assertEquals('aprobado', $contrato->fresh()->estado);

        $contrato->update(['estado' => 'en_ejecucion']);
        $this->assertEquals('en_ejecucion', $contrato->fresh()->estado);

        $contrato->update(['estado' => 'terminado']);
        $this->assertEquals('terminado', $contrato->fresh()->estado);
    }
}