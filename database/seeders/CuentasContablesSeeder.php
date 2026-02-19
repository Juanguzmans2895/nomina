<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CuentasContablesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Creando cuentas contables...');

        $now = now();
        $id  = fn(string $codigo) => DB::table('cuentas_contables')->where('codigo', $codigo)->value('id');

        $insertar = function(array $c, int $nivel, ?int $padreId) use ($now) {
            if (DB::table('cuentas_contables')->where('codigo', $c['codigo'])->exists()) {
                $this->command->line("  ⏭️  {$c['codigo']} ya existe");
                return;
            }
            DB::table('cuentas_contables')->insert([
                'codigo'              => $c['codigo'],
                'nombre'              => $c['nombre'],
                'tipo'                => $c['tipo'],
                'naturaleza'          => $c['naturaleza'],
                'nivel'               => $nivel,
                'cuenta_padre_id'     => $padreId,
                'maneja_tercero'      => $c['mt']  ?? false,
                'maneja_centro_costo' => $c['mcc'] ?? false,
                'activa'              => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
            $this->command->line("  ✅ {$c['codigo']} - {$c['nombre']}");
        };

        // ── Nivel 1 (raíces) ──────────────────────────────────────
        foreach ([
            ['codigo'=>'110000','nombre'=>'Efectivo y Equivalentes','tipo'=>'activo', 'naturaleza'=>'debito' ],
            ['codigo'=>'230000','nombre'=>'Cuentas por Pagar',      'tipo'=>'pasivo', 'naturaleza'=>'credito'],
            ['codigo'=>'250000','nombre'=>'Obligaciones Laborales',  'tipo'=>'pasivo', 'naturaleza'=>'credito'],
            ['codigo'=>'510000','nombre'=>'Gastos de Personal',      'tipo'=>'gasto',  'naturaleza'=>'debito', 'mcc'=>true],
        ] as $c) { $insertar($c, 1, null); }

        // ── Nivel 2 ───────────────────────────────────────────────
        foreach ([
            ['codigo'=>'111000','nombre'=>'Bancos',                       'tipo'=>'activo', 'naturaleza'=>'debito',  'padre'=>'110000'],
            ['codigo'=>'237000','nombre'=>'Retenciones y Aportes Nómina', 'tipo'=>'pasivo', 'naturaleza'=>'credito', 'padre'=>'230000'],
            ['codigo'=>'250500','nombre'=>'Salarios por Pagar',           'tipo'=>'pasivo', 'naturaleza'=>'credito', 'padre'=>'250000','mt'=>true],
            ['codigo'=>'251000','nombre'=>'Cesantías Consolidadas',       'tipo'=>'pasivo', 'naturaleza'=>'credito', 'padre'=>'250000'],
            ['codigo'=>'251005','nombre'=>'Intereses sobre Cesantías',    'tipo'=>'pasivo', 'naturaleza'=>'credito', 'padre'=>'250000'],
            ['codigo'=>'252000','nombre'=>'Prima de Servicios',           'tipo'=>'pasivo', 'naturaleza'=>'credito', 'padre'=>'250000'],
            ['codigo'=>'253000','nombre'=>'Vacaciones Consolidadas',      'tipo'=>'pasivo', 'naturaleza'=>'credito', 'padre'=>'250000'],
            ['codigo'=>'510506','nombre'=>'Sueldos',                      'tipo'=>'gasto',  'naturaleza'=>'debito',  'padre'=>'510000','mt'=>true,'mcc'=>true],
            ['codigo'=>'510524','nombre'=>'Aportes Salud Empleador',      'tipo'=>'gasto',  'naturaleza'=>'debito',  'padre'=>'510000','mt'=>true,'mcc'=>true],
            ['codigo'=>'510527','nombre'=>'Aportes Pensión Empleador',    'tipo'=>'gasto',  'naturaleza'=>'debito',  'padre'=>'510000','mt'=>true,'mcc'=>true],
            ['codigo'=>'510536','nombre'=>'Aportes ARL',                  'tipo'=>'gasto',  'naturaleza'=>'debito',  'padre'=>'510000','mt'=>true,'mcc'=>true],
            ['codigo'=>'510542','nombre'=>'Auxilio de Transporte',        'tipo'=>'gasto',  'naturaleza'=>'debito',  'padre'=>'510000','mt'=>true,'mcc'=>true],
            ['codigo'=>'510560','nombre'=>'Cesantías Gasto',              'tipo'=>'gasto',  'naturaleza'=>'debito',  'padre'=>'510000','mcc'=>true],
            ['codigo'=>'510563','nombre'=>'Intereses Cesantías Gasto',    'tipo'=>'gasto',  'naturaleza'=>'debito',  'padre'=>'510000','mcc'=>true],
            ['codigo'=>'510566','nombre'=>'Prima de Servicios Gasto',     'tipo'=>'gasto',  'naturaleza'=>'debito',  'padre'=>'510000','mcc'=>true],
            ['codigo'=>'510568','nombre'=>'Aportes Parafiscales',         'tipo'=>'gasto',  'naturaleza'=>'debito',  'padre'=>'510000','mt'=>true,'mcc'=>true],
            ['codigo'=>'510575','nombre'=>'Vacaciones Gasto',             'tipo'=>'gasto',  'naturaleza'=>'debito',  'padre'=>'510000','mcc'=>true],
        ] as $c) { $insertar($c, 2, $id($c['padre'])); }

        // ── Nivel 3 ───────────────────────────────────────────────
        foreach ([
            ['codigo'=>'237005','nombre'=>'Aportes Salud por Pagar',   'tipo'=>'pasivo','naturaleza'=>'credito','padre'=>'237000','mt'=>true],
            ['codigo'=>'237010','nombre'=>'Aportes Pensión por Pagar', 'tipo'=>'pasivo','naturaleza'=>'credito','padre'=>'237000','mt'=>true],
            ['codigo'=>'237015','nombre'=>'Retención en la Fuente',    'tipo'=>'pasivo','naturaleza'=>'credito','padre'=>'237000','mt'=>true],
            ['codigo'=>'237025','nombre'=>'ARL por Pagar',             'tipo'=>'pasivo','naturaleza'=>'credito','padre'=>'237000','mt'=>true],
            ['codigo'=>'237035','nombre'=>'Parafiscales por Pagar',    'tipo'=>'pasivo','naturaleza'=>'credito','padre'=>'237000','mt'=>true],
        ] as $c) { $insertar($c, 3, $id($c['padre'])); }

        $this->command->info('✅ Total cuentas en BD: ' . DB::table('cuentas_contables')->count());
    }
}