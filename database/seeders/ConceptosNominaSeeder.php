<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConceptosNominaSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Creando conceptos de nómina con recargos 2026...');
        
        $now = now();
        $conceptos = [
            
            // DEVENGOS BÁSICOS
            [
                'codigo' => 'DEV001',
                'nombre' => 'Salario Básico',
                'clasificacion' => 'devengado',
                'tipo' => 'fijo',
                'base_salarial' => true,
                'afecta_prestaciones' => true,
                'afecta_seguridad_social' => true,
                'afecta_parafiscales' => true,
                'orden_colilla' => 1,
                'visible_colilla' => true,
            ],
            [
                'codigo' => 'DEV002',
                'nombre' => 'Auxilio de Transporte',
                'clasificacion' => 'devengado',
                'tipo' => 'fijo',
                'formula' => '162000',
                'base_salarial' => false,
                'afecta_prestaciones' => true,
                'afecta_seguridad_social' => false,
                'afecta_parafiscales' => false,
                'orden_colilla' => 2,
                'visible_colilla' => true,
            ],
            
            // HORAS EXTRAS Y RECARGOS 2026
            [
                'codigo' => 'HED',
                'nombre' => 'Horas Extras Diurnas',
                'descripcion' => 'Recargo 25% (6am-10pm)',
                'clasificacion' => 'devengado',
                'tipo' => 'calculado',
                'formula' => '(salario_basico/240)*1.25*cantidad',
                'porcentaje' => 25.00,
                'base_salarial' => true,
                'afecta_prestaciones' => true,
                'afecta_seguridad_social' => true,
                'afecta_parafiscales' => true,
                'orden_colilla' => 10,
                'visible_colilla' => true,
            ],
            [
                'codigo' => 'HEN',
                'nombre' => 'Horas Extras Nocturnas',
                'descripcion' => 'Recargo 75% (10pm-6am)',
                'clasificacion' => 'devengado',
                'tipo' => 'calculado',
                'formula' => '(salario_basico/240)*1.75*cantidad',
                'porcentaje' => 75.00,
                'base_salarial' => true,
                'afecta_prestaciones' => true,
                'afecta_seguridad_social' => true,
                'afecta_parafiscales' => true,
                'orden_colilla' => 11,
                'visible_colilla' => true,
            ],
            [
                'codigo' => 'RN',
                'nombre' => 'Recargo Nocturno',
                'descripcion' => 'Recargo 35% ordinario nocturno',
                'clasificacion' => 'devengado',
                'tipo' => 'calculado',
                'formula' => '(salario_basico/240)*1.35*cantidad',
                'porcentaje' => 35.00,
                'base_salarial' => true,
                'afecta_prestaciones' => true,
                'afecta_seguridad_social' => true,
                'afecta_parafiscales' => true,
                'orden_colilla' => 12,
                'visible_colilla' => true,
            ],
            [
                'codigo' => 'HEDF',
                'nombre' => 'HE Dominical/Festivo Diurna',
                'descripcion' => 'Recargo 100%',
                'clasificacion' => 'devengado',
                'tipo' => 'calculado',
                'formula' => '(salario_basico/240)*2.00*cantidad',
                'porcentaje' => 100.00,
                'base_salarial' => true,
                'afecta_prestaciones' => true,
                'afecta_seguridad_social' => true,
                'afecta_parafiscales' => true,
                'orden_colilla' => 13,
                'visible_colilla' => true,
            ],
            [
                'codigo' => 'HENF',
                'nombre' => 'HE Dominical/Festivo Nocturna',
                'descripcion' => 'Recargo 150%',
                'clasificacion' => 'devengado',
                'tipo' => 'calculado',
                'formula' => '(salario_basico/240)*2.50*cantidad',
                'porcentaje' => 150.00,
                'base_salarial' => true,
                'afecta_prestaciones' => true,
                'afecta_seguridad_social' => true,
                'afecta_parafiscales' => true,
                'orden_colilla' => 14,
                'visible_colilla' => true,
            ],
            [
                'codigo' => 'RDF',
                'nombre' => 'Recargo Dominical/Festivo Diurno',
                'descripcion' => 'Recargo 75%',
                'clasificacion' => 'devengado',
                'tipo' => 'calculado',
                'formula' => '(salario_basico/240)*1.75*cantidad',
                'porcentaje' => 75.00,
                'base_salarial' => true,
                'afecta_prestaciones' => true,
                'afecta_seguridad_social' => true,
                'afecta_parafiscales' => true,
                'orden_colilla' => 15,
                'visible_colilla' => true,
            ],
            [
                'codigo' => 'RNDF',
                'nombre' => 'Recargo Dominical/Festivo Nocturno',
                'descripcion' => 'Recargo 110%',
                'clasificacion' => 'devengado',
                'tipo' => 'calculado',
                'formula' => '(salario_basico/240)*2.10*cantidad',
                'porcentaje' => 110.00,
                'base_salarial' => true,
                'afecta_prestaciones' => true,
                'afecta_seguridad_social' => true,
                'afecta_parafiscales' => true,
                'orden_colilla' => 16,
                'visible_colilla' => true,
            ],
            
            // OTROS DEVENGOS
            [
                'codigo' => 'BONO',
                'nombre' => 'Bonificación',
                'clasificacion' => 'devengado',
                'tipo' => 'novedad',
                'base_salarial' => false,
                'afecta_prestaciones' => false,
                'afecta_seguridad_social' => false,
                'afecta_parafiscales' => false,
                'orden_colilla' => 20,
                'visible_colilla' => true,
            ],
            [
                'codigo' => 'COMISION',
                'nombre' => 'Comisiones',
                'clasificacion' => 'devengado',
                'tipo' => 'novedad',
                'base_salarial' => true,
                'afecta_prestaciones' => true,
                'afecta_seguridad_social' => true,
                'afecta_parafiscales' => true,
                'orden_colilla' => 21,
                'visible_colilla' => true,
            ],
            
            // DEDUCCIONES
            [
                'codigo' => 'DED001',
                'nombre' => 'Salud Empleado',
                'descripcion' => '4%',
                'clasificacion' => 'deducido',
                'tipo' => 'calculado',
                'porcentaje' => 4.00,
                'formula' => 'ibc*0.04',
                'aplica_retencion' => false,
                'orden_colilla' => 50,
                'visible_colilla' => true,
            ],
            [
                'codigo' => 'DED002',
                'nombre' => 'Pensión Empleado',
                'descripcion' => '4%',
                'clasificacion' => 'deducido',
                'tipo' => 'calculado',
                'porcentaje' => 4.00,
                'formula' => 'ibc*0.04',
                'aplica_retencion' => false,
                'orden_colilla' => 51,
                'visible_colilla' => true,
            ],
            [
                'codigo' => 'RETENCION',
                'nombre' => 'Retención en la Fuente',
                'clasificacion' => 'deducido',
                'tipo' => 'novedad',
                'aplica_retencion' => true,
                'orden_colilla' => 52,
                'visible_colilla' => true,
            ],
            [
                'codigo' => 'PRESTAMO',
                'nombre' => 'Descuento Préstamo',
                'clasificacion' => 'deducido',
                'tipo' => 'novedad',
                'orden_colilla' => 53,
                'visible_colilla' => true,
            ],
        ];

        $insertados = 0;
        foreach ($conceptos as $c) {
            if (DB::table('conceptos_nomina')->where('codigo', $c['codigo'])->exists()) continue;
            DB::table('conceptos_nomina')->insert(array_merge(
                $c,
                [
                    'activo' => true,
                    'sistema' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            ));
            $insertados++;
        }

        $this->command->info("✅ Conceptos creados: {$insertados}");
        $this->command->table(['Código', 'Concepto', 'Recargo'], [
            ['HED', 'Hora Extra Diurna', '25%'],
            ['HEN', 'Hora Extra Nocturna', '75%'],
            ['RN', 'Recargo Nocturno', '35%'],
            ['HEDF', 'HE Dom/Fest Diurna', '100%'],
            ['HENF', 'HE Dom/Fest Nocturna', '150%'],
            ['RDF', 'Recargo Dom/Fest Diurno', '75%'],
            ['RNDF', 'Recargo Dom/Fest Nocturno', '110%'],
        ]);
    }
}