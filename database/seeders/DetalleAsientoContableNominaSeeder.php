<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetalleAsientoContableNominaSeeder extends Seeder
{
    private array $cIds = [];

    public function run(): void
    {
        $this->command->info('🔄 Generando detalles de asientos contables...');

        // ── Detectar tabla de detalles de nómina ─────────────────
        $tablaDetalles = null;
        foreach (['nomina_detalles', 'detalles_nomina', 'detalle_nominas'] as $t) {
            if (DB::getSchemaBuilder()->hasTable($t)) {
                $tablaDetalles = $t;
                break;
            }
        }
        if (!$tablaDetalles) {
            $this->command->error('❌ No se encontró tabla de detalles de nómina.');
            return;
        }
        $this->command->info("  📋 Tabla detalles nómina: {$tablaDetalles}");

        // ── Verificar/cargar cuentas contables ────────────────────
        $codigos = ['510506','510524','510527','510536','510568','250500','237005','237010','237025','237035'];

        $this->command->line('  📋 Verificando cuentas contables...');
        foreach ($codigos as $codigo) {
            $cId = DB::table('cuentas_contables')->where('codigo', $codigo)->value('id');
            if (!$cId) {
                $this->command->error("  ❌ Cuenta {$codigo} no encontrada. Ejecuta CuentasContablesSeeder primero.");
                return;
            }
            $this->cIds[$codigo] = $cId;
            $this->command->line("  ✔ {$codigo} → ID {$cId}");
        }

        // ── Obtener asientos ──────────────────────────────────────
        $asientos = DB::table('asientos_contables_nomina')
            ->whereNull('deleted_at')
            ->whereNotNull('nomina_id')
            ->get();

        if ($asientos->isEmpty()) {
            $this->command->warn('⚠️ No hay asientos. Ejecuta AsientosContablesSeeder primero.');
            return;
        }

        $this->command->info("📋 Procesando {$asientos->count()} asientos...");
        $totalLineas = 0;
        $omitidos    = 0;

        foreach ($asientos as $asiento) {
            // Evitar duplicados
            if (DB::table('detalles_asientos_nomina')->where('asiento_id', $asiento->id)->exists()) {
                $omitidos++;
                $this->command->line("  ⏭️  Asiento {$asiento->numero_asiento} ya tiene detalles");
                continue;
            }

            // Obtener empleados de la nómina
            $empleados = DB::table($tablaDetalles)
                ->join('empleados', "{$tablaDetalles}.empleado_id", '=', 'empleados.id')
                ->where("{$tablaDetalles}.nomina_id", $asiento->nomina_id)
                ->when(DB::getSchemaBuilder()->hasColumn($tablaDetalles, 'deleted_at'),
                    fn($q) => $q->whereNull("{$tablaDetalles}.deleted_at")
                )
                ->select(
                    "{$tablaDetalles}.empleado_id",
                    "{$tablaDetalles}.salario_basico",
                    "{$tablaDetalles}.total_neto",
                    'empleados.numero_documento',
                    DB::raw("TRIM(CONCAT(
                        COALESCE(empleados.primer_nombre,''), ' ',
                        COALESCE(empleados.segundo_nombre,''), ' ',
                        COALESCE(empleados.primer_apellido,''), ' ',
                        COALESCE(empleados.segundo_apellido,'')
                    )) AS nombre_completo")
                )
                ->get();

            if ($empleados->isEmpty()) {
                $this->command->warn("  ⚠️ Asiento {$asiento->numero_asiento}: nómina sin empleados");
                continue;
            }

            try {
                $lineas = $this->crearLineas($asiento, $empleados);
                $totalLineas += $lineas;
                $this->command->info("  ✅ {$asiento->numero_asiento} → {$lineas} líneas ({$empleados->count()} empleados)");
            } catch (\Exception $e) {
                $this->command->error("  ❌ {$asiento->numero_asiento}: " . $e->getMessage());
            }
        }

        $total = DB::table('detalles_asientos_nomina')->count();
        $this->command->info("✅ Líneas creadas: {$totalLineas} | Omitidos: {$omitidos} | Total en BD: {$total}");
    }

    private function crearLineas(object $asiento, $empleados): int
    {
        $now    = now();
        $orden  = 1;
        $lineas = 0;

        $totalSalarios         = $empleados->sum('salario_basico');
        $totalSaludEmpleado    = round($totalSalarios * 0.04,    2);
        $totalSaludEmpleador   = round($totalSalarios * 0.085,   2);
        $totalPensionEmpleado  = round($totalSalarios * 0.04,    2);
        $totalPensionEmpleador = round($totalSalarios * 0.12,    2);
        $totalARL              = round($totalSalarios * 0.00522, 2);
        $totalParafiscales     = round($totalSalarios * 0.09,    2);
        $totalSalud            = round($totalSaludEmpleado + $totalSaludEmpleador, 2);
        $totalPension          = round($totalPensionEmpleado + $totalPensionEmpleador, 2);

        // ── DÉBITOS ───────────────────────────────────────────────

        // 1. Sueldos por empleado
        foreach ($empleados as $emp) {
            DB::table('detalles_asientos_nomina')->insert([
                'asiento_id'          => $asiento->id,
                'cuenta_contable_id'  => $this->cIds['510506'],
                'codigo_cuenta'       => '510506',
                'nombre_cuenta'       => 'Sueldos',
                'empleado_id'         => $emp->empleado_id,
                'documento_tercero'   => $emp->numero_documento,
                'nombre_tercero'      => trim($emp->nombre_completo),
                'centro_costo_id'     => null,
                'codigo_centro_costo' => null,
                'debito'              => round($emp->salario_basico, 2),
                'credito'             => 0,
                'base'                => round($emp->salario_basico, 2),
                'porcentaje'          => null,
                'descripcion'         => 'Sueldo - ' . trim($emp->nombre_completo),
                'orden'               => $orden++,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
            $lineas++;
        }

        // 2-5. Aportes empleador consolidados
        $debitosConsolidados = [
            ['510524', 'Aportes Salud Empleador',   'EPS',              $totalSaludEmpleador,   8.50 ],
            ['510527', 'Aportes Pensión Empleador', 'Fondo Pensión',    $totalPensionEmpleador, 12.00],
            ['510536', 'Aportes ARL',               'ARL',              $totalARL,              0.522],
            ['510568', 'Aportes Parafiscales',      'SENA, ICBF, Caja', $totalParafiscales,     9.00 ],
        ];

        foreach ($debitosConsolidados as [$cod, $nom, $ter, $val, $pct]) {
            DB::table('detalles_asientos_nomina')->insert([
                'asiento_id'          => $asiento->id,
                'cuenta_contable_id'  => $this->cIds[$cod],
                'codigo_cuenta'       => $cod,
                'nombre_cuenta'       => $nom,
                'empleado_id'         => null,
                'documento_tercero'   => null,
                'nombre_tercero'      => $ter,
                'centro_costo_id'     => null,
                'codigo_centro_costo' => null,
                'debito'              => $val,
                'credito'             => 0,
                'base'                => $totalSalarios,
                'porcentaje'          => $pct,
                'descripcion'         => $nom . ' (' . $pct . '%)',
                'orden'               => $orden++,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
            $lineas++;
        }

        // ── CRÉDITOS ──────────────────────────────────────────────

        // 6. Salarios neto por empleado
        foreach ($empleados as $emp) {
            $neto = $emp->total_neto ?? round($emp->salario_basico * 0.92, 2);
            DB::table('detalles_asientos_nomina')->insert([
                'asiento_id'          => $asiento->id,
                'cuenta_contable_id'  => $this->cIds['250500'],
                'codigo_cuenta'       => '250500',
                'nombre_cuenta'       => 'Salarios por Pagar',
                'empleado_id'         => $emp->empleado_id,
                'documento_tercero'   => $emp->numero_documento,
                'nombre_tercero'      => trim($emp->nombre_completo),
                'centro_costo_id'     => null,
                'codigo_centro_costo' => null,
                'debito'              => 0,
                'credito'             => round($neto, 2),
                'base'                => round($emp->salario_basico, 2),
                'porcentaje'          => null,
                'descripcion'         => 'Salario neto - ' . trim($emp->nombre_completo),
                'orden'               => $orden++,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
            $lineas++;
        }

        // 7-10. Pasivos consolidados
        $creditosConsolidados = [
            ['237005', 'Aportes Salud por Pagar',   'EPS',              $totalSalud,        12.50],
            ['237010', 'Aportes Pensión por Pagar', 'Fondo Pensión',    $totalPension,      16.00],
            ['237025', 'ARL por Pagar',              'ARL',              $totalARL,          0.522],
            ['237035', 'Parafiscales por Pagar',     'SENA, ICBF, Caja', $totalParafiscales, 9.00 ],
        ];

        foreach ($creditosConsolidados as [$cod, $nom, $ter, $val, $pct]) {
            DB::table('detalles_asientos_nomina')->insert([
                'asiento_id'          => $asiento->id,
                'cuenta_contable_id'  => $this->cIds[$cod],
                'codigo_cuenta'       => $cod,
                'nombre_cuenta'       => $nom,
                'empleado_id'         => null,
                'documento_tercero'   => null,
                'nombre_tercero'      => $ter,
                'centro_costo_id'     => null,
                'codigo_centro_costo' => null,
                'debito'              => 0,
                'credito'             => $val,
                'base'                => $totalSalarios,
                'porcentaje'          => $pct,
                'descripcion'         => $nom,
                'orden'               => $orden++,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
            $lineas++;
        }

        return $lineas;
    }
}