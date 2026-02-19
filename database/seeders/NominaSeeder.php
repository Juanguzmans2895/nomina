<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;

class NominaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el primer usuario disponible o usar null
        $userId = User::first()?->id;
        
        // Si no hay usuarios, mostrar advertencia
        if (!$userId) {
            $this->command->warn('No hay usuarios en la base de datos. Las nóminas se crearán sin usuario asignado.');
        }
        
        // 18 nóminas: agosto 2025 - junio 2026 (últimos 6 meses + 12 meses actuales)
        $nominas = [];
        
        // Valores base para cálculos coherentes
        $totalDevengadoBase = 225000000; // 50 empleados × ~$4.5M promedio
        $deducciones = $totalDevengadoBase * 0.08; // 8% (salud 4% + pensión 4%)
        
        // Agosto 2025 a Diciembre 2025 (5 nóminas - TODAS CERRADAS Y PAGADAS)
        $nominasAnteriores = [
            ['mes' => 8, 'año' => 2025, 'estado' => 'pagada', 'periodo_id' => 5],
            ['mes' => 9, 'año' => 2025, 'estado' => 'pagada', 'periodo_id' => 6],
            ['mes' => 10, 'año' => 2025, 'estado' => 'pagada', 'periodo_id' => 7],
            ['mes' => 11, 'año' => 2025, 'estado' => 'pagada', 'periodo_id' => 8],
            ['mes' => 12, 'año' => 2025, 'estado' => 'pagada', 'periodo_id' => 9],
        ];
        
        $consecutivo = 1;
        foreach ($nominasAnteriores as $nom) {
            $fecha = Carbon::create($nom['año'], $nom['mes'], 1);
            $fechaFin = $fecha->copy()->endOfMonth();
            
            $nominas[] = [
                'numero_nomina' => sprintf('NOM-%d-%04d', $nom['año'], $consecutivo),
                'nombre' => 'Nómina Mensual ' . ucfirst($fecha->locale('es')->isoFormat('MMMM YYYY')),
                'tipo_nomina_id' => 1,
                'periodo_nomina_id' => $nom['periodo_id'],
                'fecha_inicio' => $fecha->format('Y-m-d'),
                'fecha_fin' => $fechaFin->format('Y-m-d'),
                'fecha_pago' => $fechaFin->copy()->addDays(5)->format('Y-m-d'),
                'estado' => $nom['estado'],
                'total_devengado' => round($totalDevengadoBase * (0.95 + rand(0, 10) / 100), 2),
                'total_deducciones' => round($deducciones, 2),
                'total_neto' => round($totalDevengadoBase * 0.92 - $deducciones + rand(-1000000, 1000000), 2),
                'numero_empleados' => 50,
                'pagado' => true,
                'cerrado' => true,
                'aprobado_by' => $userId,
                'fecha_aprobacion' => $fechaFin->copy()->addDays(3)->format('Y-m-d'),
                'pagado_by' => $userId,
                'fecha_pago_efectivo' => $fechaFin->copy()->addDays(5)->format('Y-m-d'),
                'fecha_pago_real' => $fechaFin->copy()->addDays(5)->format('Y-m-d'),
                'observaciones' => 'Nómina procesada y pagada completamente',
                'created_at' => $fechaFin->copy()->subDays(2),
                'updated_at' => $fechaFin->copy()->addDays(5),
            ];
            $consecutivo++;
        }
        
        // Enero 2026 a Junio 2026 (6 nóminas actuales)
        $nominasActuales = [
            ['mes' => 1, 'estado' => 'pagada', 'periodo_id' => 1, 'pagado' => true, 'cerrado' => true],
            ['mes' => 2, 'estado' => 'pagada', 'periodo_id' => 2, 'pagado' => true, 'cerrado' => true],
            ['mes' => 3, 'estado' => 'pagada', 'periodo_id' => 3, 'pagado' => true, 'cerrado' => true],
            ['mes' => 4, 'estado' => 'pagada', 'periodo_id' => 4, 'pagado' => true, 'cerrado' => false],
            ['mes' => 5, 'estado' => 'aprobada', 'periodo_id' => 5, 'pagado' => false, 'cerrado' => false],
            ['mes' => 6, 'estado' => 'prenomina', 'periodo_id' => 6, 'pagado' => false, 'cerrado' => false],
        ];
        
        foreach ($nominasActuales as $nom) {
            $fecha = Carbon::create(2026, $nom['mes'], 1);
            $fechaFin = $fecha->copy()->endOfMonth();
            $novedadesExtra = $nom['mes'] >= 5 ? rand(1000000, 5000000) : 0;
            
            $nominas[] = [
                'numero_nomina' => sprintf('NOM-2026-%04d', $consecutivo),
                'nombre' => 'Nómina Mensual ' . ucfirst($fecha->locale('es')->isoFormat('MMMM YYYY')),
                'tipo_nomina_id' => 1,
                'periodo_nomina_id' => $nom['periodo_id'],
                'fecha_inicio' => $fecha->format('Y-m-d'),
                'fecha_fin' => $fechaFin->format('Y-m-d'),
                'fecha_pago' => $nom['pagado'] ? $fechaFin->copy()->addDays(5)->format('Y-m-d') : null,
                'estado' => $nom['estado'],
                'total_devengado' => round($totalDevengadoBase + $novedadesExtra, 2),
                'total_deducciones' => round($deducciones + ($novedadesExtra * 0.08), 2),
                'total_neto' => round(($totalDevengadoBase + $novedadesExtra) - ($deducciones + ($novedadesExtra * 0.08)), 2),
                'numero_empleados' => 50,
                'pagado' => $nom['pagado'],
                'cerrado' => $nom['cerrado'],
                'aprobado_by' => $nom['estado'] != 'prenomina' ? $userId : null,
                'fecha_aprobacion' => $nom['estado'] != 'prenomina' ? $fechaFin->copy()->addDays(3)->format('Y-m-d') : null,
                'pagado_by' => $nom['pagado'] ? $userId : null,
                'fecha_pago_efectivo' => $nom['pagado'] ? $fechaFin->copy()->addDays(5)->format('Y-m-d') : null,
                'fecha_pago_real' => $nom['pagado'] ? $fechaFin->copy()->addDays(5)->format('Y-m-d') : null,
                'observaciones' => match($nom['estado']) {
                    'pagada' => 'Nómina pagada completamente',
                    'aprobada' => 'Nómina aprobada, pendiente de pago',
                    'prenomina' => 'Nómina en pre-liquidación',
                    default => null
                },
                'created_at' => $fechaFin->copy()->subDays(2),
                'updated_at' => $nom['pagado'] ? $fechaFin->copy()->addDays(5) : now(),
            ];
            $consecutivo++;
        }

        DB::table('nominas')->insert($nominas);
        
        $this->command->info('✓ ' . count($nominas) . ' nóminas creadas exitosamente (Ago 2025 - Jun 2026)');
    }
}