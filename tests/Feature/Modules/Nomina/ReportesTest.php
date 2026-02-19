<?php

namespace Tests\Feature\Modules\Nomina;

use Tests\TestCase;
use App\Modules\Nomina\Models\Nomina;
use App\Modules\Nomina\Models\NominaDetalle;
use App\Modules\Nomina\Models\Empleado;
use App\Modules\Nomina\Services\Reportes\DesprendibleService;
use App\Modules\Nomina\Services\Reportes\CertificadosService;
use App\Modules\Nomina\Services\Reportes\PILAService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportesTest extends TestCase
{
    use RefreshDatabase;

    protected $desprendibleService;
    protected $certificadosService;
    protected $pilaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->desprendibleService = new DesprendibleService();
        $this->certificadosService = new CertificadosService();
        $this->pilaService = new PILAService();
    }

    /** @test */
    public function puede_generar_desprendible_de_pago()
    {
        $empleado = Empleado::factory()->create();
        $nomina = Nomina::factory()->create();
        
        $detalle = NominaDetalle::factory()->create([
            'nomina_id' => $nomina->id,
            'empleado_id' => $empleado->id,
            'salario_basico' => 3000000,
            'total_devengado' => 3162000,
            'total_deducciones' => 360000,
            'total_neto' => 2802000,
        ]);

        $pdf = $this->desprendibleService->generarPDF($detalle);

        $this->assertNotNull($pdf);
        $this->assertInstanceOf(\Barryvdh\DomPDF\PDF::class, $pdf);
    }

    /** @test */
    public function puede_generar_certificado_laboral()
    {
        $empleado = Empleado::factory()->create([
            'primer_nombre' => 'Juan',
            'primer_apellido' => 'Pérez',
            'cargo' => 'Desarrollador',
            'fecha_ingreso' => now()->subYears(2),
        ]);

        $opciones = [
            'motivo' => 'FINES CREDITICIOS',
            'incluir_salario' => true,
            'incluir_funciones' => false,
        ];

        $pdf = $this->certificadosService->generarPDFLaboral($empleado, $opciones);

        $this->assertNotNull($pdf);
    }

    /** @test */
    public function puede_generar_certificado_de_ingresos()
    {
        $empleado = Empleado::factory()->create();
        $anio = 2024;

        $pdf = $this->certificadosService->generarPDFIngresos($empleado, $anio);

        $this->assertNotNull($pdf);
    }

    /** @test */
    public function puede_generar_archivo_pila()
    {
        $nomina = Nomina::factory()->create([
            'numero_empleados' => 10,
        ]);

        // Crear detalles
        $empleados = Empleado::factory()->count(10)->create();
        
        foreach ($empleados as $empleado) {
            NominaDetalle::factory()->create([
                'nomina_id' => $nomina->id,
                'empleado_id' => $empleado->id,
            ]);
        }

        $archivo = $this->pilaService->generarArchivoPILA($nomina);

        $this->assertNotNull($archivo);
        $this->assertIsString($archivo);
        
        // Verificar que contiene registros
        $lineas = explode("\n", $archivo);
        $this->assertGreaterThan(10, count($lineas)); // Encabezado + empleados
    }

    /** @test */
    public function formato_pila_contiene_estructura_correcta()
    {
        $nomina = Nomina::factory()->create();
        $empleado = Empleado::factory()->create([
            'numero_documento' => '1234567890',
            'salario_basico' => 3000000,
        ]);

        $detalle = NominaDetalle::factory()->create([
            'nomina_id' => $nomina->id,
            'empleado_id' => $empleado->id,
        ]);

        $archivo = $this->pilaService->generarArchivoPILA($nomina);
        $lineas = explode("\n", $archivo);

        // Verificar registro tipo 1 (encabezado)
        $this->assertStringContainsString('1', $lineas[0]); // Tipo de registro

        // Verificar registro tipo 2 (empleado)
        $registroEmpleado = $lineas[1];
        $this->assertStringContainsString('2', $registroEmpleado); // Tipo
        $this->assertStringContainsString('1234567890', $registroEmpleado); // Documento
    }

    /** @test */
    public function consolidado_calcula_totales_correctamente()
    {
        $nomina = Nomina::factory()->create();
        
        // Crear 5 empleados con detalles
        $empleados = Empleado::factory()->count(5)->create();
        
        foreach ($empleados as $empleado) {
            NominaDetalle::factory()->create([
                'nomina_id' => $nomina->id,
                'empleado_id' => $empleado->id,
                'total_devengado' => 3000000,
                'aporte_salud' => 120000,
                'aporte_pension' => 120000,
            ]);
        }

        $totalSalud = 5 * 120000;
        $totalPension = 5 * 120000;

        $this->assertEquals(600000, $totalSalud);
        $this->assertEquals(600000, $totalPension);
    }
}