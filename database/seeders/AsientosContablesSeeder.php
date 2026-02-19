<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AsientosContablesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Generando asientos contables...');

        // Obtener ID del primer usuario (puede ser null si no hay)
        $userId = DB::table('users')->value('id'); // null si no hay usuarios
        if (!$userId) {
            $this->command->warn('⚠️ No hay usuarios en BD. Los campos de auditoría quedarán en null.');
        }

        // Detectar nombre correcto de tabla de detalles de nómina
        $tablaDetalles = null;
        foreach (['nomina_detalles', 'detalles_nomina', 'detalle_nominas'] as $tabla) {
            if (DB::getSchemaBuilder()->hasTable($tabla)) {
                $tablaDetalles = $tabla;
                break;
            }
        }

        if (!$tablaDetalles) {
            $this->command->error('❌ No se encontró tabla de detalles de nómina.');
            return;
        }
        $this->command->info("  📋 Tabla detalles detectada: {$tablaDetalles}");

        $nominas = DB::table('nominas')->whereNull('deleted_at')->get();

        if ($nominas->isEmpty()) {
            $this->command->warn('⚠️ No hay nóminas.');
            return;
        }

        $contadores = [];
        $totalCreados = 0;

        foreach ($nominas as $nomina) {
            // Evitar duplicados
            if (DB::table('asientos_contables_nomina')->where('nomina_id', $nomina->id)->exists()) {
                $this->command->line("  ⏭️  Nómina {$nomina->numero_nomina} ya tiene asiento");
                continue;
            }

            // Obtener detalles
            $detalles = DB::table($tablaDetalles)
                ->where('nomina_id', $nomina->id)
                ->when(DB::getSchemaBuilder()->hasColumn($tablaDetalles, 'deleted_at'),
                    fn($q) => $q->whereNull('deleted_at')
                )
                ->get();

            if ($detalles->isEmpty()) {
                $this->command->warn("  ⚠️ {$nomina->numero_nomina} sin detalles, omitida");
                continue;
            }

            // Calcular totales
            $totalSalarios         = $detalles->sum('salario_basico');
            $totalSaludEmpleador   = round($totalSalarios * 0.085,   2);
            $totalPensionEmpleador = round($totalSalarios * 0.12,    2);
            $totalARL              = round($totalSalarios * 0.00522, 2);
            $totalParafiscales     = round($totalSalarios * 0.09,    2);
            $totalSaludEmpleado    = round($totalSalarios * 0.04,    2);
            $totalPensionEmpleado  = round($totalSalarios * 0.04,    2);
            $salarioNeto           = round($totalSalarios - $totalSaludEmpleado - $totalPensionEmpleado, 2);
            $totalSalud            = round($totalSaludEmpleado + $totalSaludEmpleador, 2);
            $totalPension          = round($totalPensionEmpleado + $totalPensionEmpleador, 2);

            $totalDebito  = round($totalSalarios + $totalSaludEmpleador + $totalPensionEmpleador + $totalARL + $totalParafiscales, 2);
            $totalCredito = round($salarioNeto + $totalSalud + $totalPension + $totalARL + $totalParafiscales, 2);
            $diferencia   = round($totalDebito - $totalCredito, 2);

            // Fecha y período
            $fechaAsiento    = $nomina->fecha_pago ?? $nomina->created_at;
            $periodoContable = Carbon::parse($fechaAsiento)->format('Y-m');
            $yyyymm          = Carbon::parse($fechaAsiento)->format('Ym');
            $key             = "CN_{$yyyymm}";

            if (!isset($contadores[$key])) {
                $ultimo = DB::table('asientos_contables_nomina')
                    ->where('tipo_asiento', 'causacion_nomina')
                    ->where('periodo_contable', $periodoContable)
                    ->orderByDesc('id')->value('numero_asiento');
                $contadores[$key] = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;
            } else {
                $contadores[$key]++;
            }

            $numeroAsiento = 'CN-' . $yyyymm . '-' . str_pad($contadores[$key], 4, '0', STR_PAD_LEFT);
            $now = now();

            try {
                DB::table('asientos_contables_nomina')->insert([
                    'numero_asiento'         => $numeroAsiento,
                    'fecha_asiento'          => Carbon::parse($fechaAsiento)->toDateString(),
                    'periodo_contable'       => $periodoContable,
                    'descripcion'            => "Causación nómina {$nomina->numero_nomina} – período {$periodoContable}",
                    'nomina_id'              => $nomina->id,
                    'tipo_asiento'           => 'causacion_nomina',
                    'total_debito'           => $totalDebito,
                    'total_credito'          => $totalCredito,
                    'diferencia'             => $diferencia,
                    'cuadrado'               => abs($diferencia) < 0.01 ? 1 : 0,
                    'estado'                 => 'contabilizado',
                    // ✅ FK de usuarios solo si hay usuarios en BD
                    'aprobado_by'            => $userId,
                    'fecha_aprobacion'       => $userId ? $now : null,
                    'contabilizado_by'       => $userId,
                    'fecha_contabilizacion'  => $userId ? $now : null,
                    'created_by'             => $userId,
                    'updated_by'             => $userId,
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ]);

                $totalCreados++;
                $this->command->info(sprintf(
                    '  ✅ %s | Débito: $%s | Crédito: $%s | %s',
                    $numeroAsiento,
                    number_format($totalDebito, 0),
                    number_format($totalCredito, 0),
                    abs($diferencia) < 0.01 ? '✔ Cuadrado' : "⚠ Dif: \${$diferencia}"
                ));

            } catch (\Exception $e) {
                $this->command->error("  ❌ Error {$nomina->numero_nomina}: " . $e->getMessage());
            }
        }

        $this->command->info("✅ Asientos creados: {$totalCreados} | Total en BD: " . DB::table('asientos_contables_nomina')->count());
    }
}