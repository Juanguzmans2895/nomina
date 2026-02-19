<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seeders con orden de dependencias...');
        $this->command->newLine();

        $this->call([
            // ── 1. USUARIOS (base de todo) ──────────────────────
            UserSeeder::class,

            // ── 2. CATÁLOGOS BASE (sin dependencias) ────────────
            TipoNominaSeeder::class,
            PeriodoNominaSeeder::class,
            ConceptosNominaSeeder::class,
            CentrosCostoSeeder::class,

            // ── 3. CONTABILIDAD (DEBE IR ANTES de asientos) ────
            CuentasContablesSeeder::class,

            // ── 4. EMPLEADOS (base transaccional) ────────────────
            EmpleadoSeeder::class,
            ContratosSeeder::class,

            // ── 5. NÓMINAS (depende de Empleados) ───────────────
            NominaSeeder::class,
            DetalleNominaSeeder::class,
            NovedadNominaSeeder::class,
            ProvisionesSeeder::class,

            // ── 6. CONTABILIDAD (depende de nóminas) ─────────────
            AsientosContablesSeeder::class,           // DEBE IR ANTES de detalles
            DetalleAsientoContableNominaSeeder::class, // DEPENDE de asientos y cuentas
        ]);

        $this->command->newLine();
        $this->command->info('✅ ¡Seeders ejecutados exitosamente!');
        $this->command->newLine();
        $this->command->line('📊 Resumen de datos creados:');
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();
        $this->command->line('📁 CATÁLOGOS:');
        $this->command->line('   ├─ ' . \App\Models\User::count() . ' Usuarios');
        $this->command->line('   ├─ ' . \App\Modules\Nomina\Models\TipoNomina::count() . ' Tipos de nómina');
        $this->command->line('   ├─ ' . \App\Modules\Nomina\Models\PeriodoNomina::count() . ' Períodos (36: 2024-2026)');
        $this->command->line('   ├─ ' . \App\Modules\Nomina\Models\ConceptoNomina::count() . ' Conceptos');
        $this->command->line('   └─ ' . \App\Modules\Nomina\Models\CentroCosto::count() . ' Centros de costo (25)');
        
        $this->command->newLine();
        $this->command->line('👥 RECURSOS:');
        $this->command->line('   ├─ ' . \App\Modules\Nomina\Models\Empleado::count() . ' Empleados (50)');
        $this->command->line('   └─ ' . \App\Modules\Nomina\Models\Contrato::count() . ' Contratos (15)');
        
        $this->command->newLine();
        $this->command->line('💰 NÓMINAS:');
        $this->command->line('   ├─ ' . \DB::table('nominas')->count() . ' Nóminas (11: Ago 2025 - Jun 2026)');
        $this->command->line('   ├─ ' . \DB::table('nomina_detalles')->count() . ' Detalles de nómina (300)');
        $this->command->line('   ├─ ' . \DB::table('novedades_nomina')->count() . ' Novedades (116+)');
        $this->command->line('   └─ ' . \DB::table('provisiones')->count() . ' Provisiones (50)');
        
        $this->command->newLine();
        $this->command->line('📊 CONTABILIDAD:');
        $this->command->line('   ├─ ' . \DB::table('cuentas_contables')->count() . ' Cuentas contables');
        $this->command->line('   ├─ ' . \DB::table('asientos_contables_nomina')->count() . ' Asientos contables (6)');
        $this->command->line('   └─ ' . \DB::table('detalles_asientos_nomina')->count() . ' Líneas de asientos (648+)');
        
        $this->command->newLine();
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->line('🎯 TOTAL: ~1,000+ registros COHERENTES generados');
        $this->command->newLine();
    }
}