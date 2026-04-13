<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConceptosNominaSeeder extends Seeder
{
    /**
     * Seeder adaptado a estructura LIQUIDACION DE NOMINA ENERO 2026
     * 48 conceptos que conforman devengos, deducciones, aportes y provisiones
     */
    public function run(): void
    {
        $this->command->info('🔄 Creando 48 conceptos de nómina según documento Excel 2026...');
        
        $now = now();
        $conceptos = $this->obtenerConceptosCompletos();

        $insertados = 0;
        foreach ($conceptos as $c) {
            if (DB::table('conceptos_nomina')->where('codigo', $c['codigo'])->exists()) continue;
            DB::table('conceptos_nomina')->insert(array_merge(
                $c,
                [
                    'activo' => true,
                    'sistema' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            ));
            $insertados++;
        }

        $this->command->info("✅ {$insertados} conceptos creados exitosamente");
        $this->command->line('');
        $this->command->info('📋 ESTRUCTURA DE CONCEPTOS:');
        $this->command->table(['Sección', 'Concepto', 'Código', 'Tipo'], [
            ['DEVENGOS', 'Salario Básico', 'SALARIO', 'Base'],
            ['DEVENGOS', 'Auxilio Transporte', 'AUX_TRANSP', 'Base'],
            ['DEVENGOS', 'Horas Extras Diurnas', 'HED', '+25%'],
            ['DEVENGOS', 'Horas Extras Nocturnas', 'HEN', '+75%'],
            ['DEVENGOS', 'Recargo Nocturno', 'RN', '+35%'],
            ['DEVENGOS', 'Recargo Dom/Fest Diurno', 'RDF', '+75%'],
            ['DEVENGOS', 'Recargo Dom/Fest Nocturno', 'RNDF', '+110%'],
            ['DEDUCCIONES', 'Salud Empleado', 'SALUD_EMP', '-4%'],
            ['DEDUCCIONES', 'Pensión Empleado', 'PENSION_EMP', '-4%'],
            ['APORTES', 'Salud Empleador', 'SALUD_PATR', '+8.5%'],
            ['APORTES', 'Pensión Empleador', 'PENSION_PATR', '+12%'],
            ['PROVISIONES', 'Cesantías', 'CESANTIAS', '1/12'],
            ['PROVISIONES', 'Prima de Servicios', 'PRIMA', '1/12'],
            ['PROVISIONES', 'Vacaciones', 'VACACIONES', '1/24'],
        ]);
    }

    /**
     * Obtener array completo de 48 conceptos según Excel
     */
    private function obtenerConceptosCompletos(): array
    {
        return [
            // ═══════════════════════════════════════════════════════════
            // SECCIÓN 1: DEVENGOS (13 conceptos)
            // ═══════════════════════════════════════════════════════════
            
            // Base
            [
                'codigo' => 'SALARIO',
                'nombre' => 'Salario Básico',
                'descripcion' => 'Salario Plan de Cargos / 30 * Días Laborados',
                'clasificacion' => 'devengado',
                'tipo' => 'fijo',
                'base_salarial' => true,
                'afecta_prestaciones' => true,
                'afecta_seguridad_social' => true,
                'afecta_parafiscales' => true,
                'orden_colilla' => 1,
                'visible_colilla' => true,
                'agrupador' => 'DEVENGOS',
            ],
            [
                'codigo' => 'AUX_TRANSP',
                'nombre' => 'Auxilio de Transporte',
                'descripcion' => '249,095 / 30 * Días Laborados (si salario < SMLV)',
                'clasificacion' => 'devengado',
                'tipo' => 'calculado',
                'porcentaje' => 0.00,
                'base_salarial' => false,
                'afecta_prestaciones' => true,
                'afecta_seguridad_social' => false,
                'afecta_parafiscales' => false,
                'orden_colilla' => 2,
                'visible_colilla' => true,
                'agrupador' => 'DEVENGOS',
            ],

            // Horas Extras
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
                'agrupador' => 'DEVENGOS',
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
                'agrupador' => 'DEVENGOS',
            ],
            [
                'codigo' => 'HED_DOM_FEST',
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
                'orden_colilla' => 12,
                'visible_colilla' => true,
                'agrupador' => 'DEVENGOS',
            ],
            [
                'codigo' => 'HEN_DOM_FEST',
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
                'orden_colilla' => 13,
                'visible_colilla' => true,
                'agrupador' => 'DEVENGOS',
            ],

            // Recargos
            [
                'codigo' => 'RN',
                'nombre' => 'Recargo Nocturno',
                'descripcion' => 'Recargo 35% (10pm-6am ordinario)',
                'clasificacion' => 'devengado',
                'tipo' => 'calculado',
                'formula' => '(salario_basico/240)*1.35*cantidad',
                'porcentaje' => 35.00,
                'base_salarial' => true,
                'afecta_prestaciones' => true,
                'afecta_seguridad_social' => true,
                'afecta_parafiscales' => true,
                'orden_colilla' => 14,
                'visible_colilla' => true,
                'agrupador' => 'DEVENGOS',
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
                'agrupador' => 'DEVENGOS',
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
                'agrupador' => 'DEVENGOS',
            ],

            // Otros devengos
            [
                'codigo' => 'BONIFICACION',
                'nombre' => 'Reintegro / Bonificaciones',
                'clasificacion' => 'devengado',
                'tipo' => 'novedad',
                'base_salarial' => true,
                'afecta_prestaciones' => true,
                'afecta_seguridad_social' => true,
                'afecta_parafiscales' => true,
                'orden_colilla' => 17,
                'visible_colilla' => true,
                'agrupador' => 'DEVENGOS',
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
                'orden_colilla' => 18,
                'visible_colilla' => true,
                'agrupador' => 'DEVENGOS',
            ],

            // ═══════════════════════════════════════════════════════════
            // SECCIÓN 2: DEDUCCIONES (19 conceptos)
            // ═══════════════════════════════════════════════════════════

            // Seguridad Social Empleado (obligatorias)
            [
                'codigo' => 'SALUD_EMP',
                'nombre' => 'Salud Empleado',
                'descripcion' => 'Aporte a EPS - 4% del IBC',
                'clasificacion' => 'deducido',
                'tipo' => 'calculado',
                'porcentaje' => 4.00,
                'formula' => 'ibc*0.04',
                'aplica_retencion' => false,
                'orden_colilla' => 30,
                'visible_colilla' => true,
                'agrupador' => 'DEDUCCIONES',
            ],
            [
                'codigo' => 'PENSION_EMP',
                'nombre' => 'Pensión Empleado',
                'descripcion' => 'Aporte a AFP - 4% del IBC',
                'clasificacion' => 'deducido',
                'tipo' => 'calculado',
                'porcentaje' => 4.00,
                'formula' => 'ibc*0.04',
                'aplica_retencion' => false,
                'orden_colilla' => 31,
                'visible_colilla' => true,
                'agrupador' => 'DEDUCCIONES',
            ],
            [
                'codigo' => 'FONDO_SOLIDARIDAD',
                'nombre' => 'Fondo de Solidaridad Pensional',
                'descripcion' => '1% - 1.4% según rango salarial',
                'clasificacion' => 'deducido',
                'tipo' => 'calculado',
                'porcentaje' => 1.00,
                'aplica_retencion' => false,
                'orden_colilla' => 32,
                'visible_colilla' => true,
                'agrupador' => 'DEDUCCIONES',
            ],

            // Retención en la Fuente
            [
                'codigo' => 'RTEFTE',
                'nombre' => 'Retención en la Fuente',
                'descripcion' => 'Retención en la fuente DIAN',
                'clasificacion' => 'deducido',
                'tipo' => 'novedad',
                'aplica_retencion' => true,
                'orden_colilla' => 33,
                'visible_colilla' => true,
                'agrupador' => 'DEDUCCIONES',
            ],

            // Otros descuentos (voluntarios y exógenos)
            [
                'codigo' => 'DESC_DONACION',
                'nombre' => 'Descuento Donación',
                'clasificacion' => 'deducido',
                'tipo' => 'novedad',
                'orden_colilla' => 34,
                'visible_colilla' => true,
                'agrupador' => 'DEDUCCIONES',
            ],
            [
                'codigo' => 'DESC_COMFENALCO',
                'nombre' => 'Descuento Créditos Comfenalco',
                'clasificacion' => 'deducido',
                'tipo' => 'novedad',
                'orden_colilla' => 35,
                'visible_colilla' => true,
                'agrupador' => 'DEDUCCIONES',
            ],
            [
                'codigo' => 'DESC_PRESTANOMINA',
                'nombre' => 'Descuento Prestanómina',
                'descripcion' => 'Descuento por préstamo de nómina',
                'clasificacion' => 'deducido',
                'tipo' => 'novedad',
                'orden_colilla' => 36,
                'visible_colilla' => true,
                'agrupador' => 'DEDUCCIONES',
            ],
            [
                'codigo' => 'DESC_BANCO_OCCIDENTE',
                'nombre' => 'Descuento Banco Occidente',
                'clasificacion' => 'deducido',
                'tipo' => 'novedad',
                'orden_colilla' => 37,
                'visible_colilla' => true,
                'agrupador' => 'DEDUCCIONES',
            ],
            [
                'codigo' => 'DESC_OLIVOS',
                'nombre' => 'Descuento Olivos',
                'clasificacion' => 'deducido',
                'tipo' => 'novedad',
                'orden_colilla' => 38,
                'visible_colilla' => true,
                'agrupador' => 'DEDUCCIONES',
            ],
            [
                'codigo' => 'DESC_POLIZA_FEPASDE',
                'nombre' => 'Descuento Póliza FEPASDE',
                'descripcion' => 'Descuento por póliza de seguros',
                'clasificacion' => 'deducido',
                'tipo' => 'novedad',
                'orden_colilla' => 39,
                'visible_colilla' => true,
                'agrupador' => 'DEDUCCIONES',
            ],
            [
                'codigo' => 'DESC_SEGUROS_BOLIVAR',
                'nombre' => 'Descuento Seguros Bolívar',
                'clasificacion' => 'deducido',
                'tipo' => 'novedad',
                'orden_colilla' => 40,
                'visible_colilla' => true,
                'agrupador' => 'DEDUCCIONES',
            ],
            [
                'codigo' => 'OTROS_DESCUENTOS',
                'nombre' => 'Otros Descuentos de Nómina',
                'descripcion' => 'Otros descuentos no catalogados',
                'clasificacion' => 'deducido',
                'tipo' => 'novedad',
                'orden_colilla' => 41,
                'visible_colilla' => true,
                'agrupador' => 'DEDUCCIONES',
            ],
            [
                'codigo' => 'APORTE_VOL_PENSION',
                'nombre' => 'Aporte Voluntario Pensión',
                'descripcion' => 'Aporte voluntario a la pensión',
                'clasificacion' => 'deducido',
                'tipo' => 'novedad',
                'orden_colilla' => 42,
                'visible_colilla' => true,
                'agrupador' => 'DEDUCCIONES',
            ],

            // ═══════════════════════════════════════════════════════════
            // SECCIÓN 3: APORTES EMPLEADOR / PARAFISCALES (6 conceptos)
            // ═══════════════════════════════════════════════════════════

            [
                'codigo' => 'SALUD_PATR',
                'nombre' => 'Salud Empleador',
                'descripcion' => 'Aporte EPS empleador - 8.5%',
                'clasificacion' => 'no_imputable',
                'tipo' => 'calculado',
                'porcentaje' => 8.50,
                'base_salarial' => true,
                'afecta_prestaciones' => false,
                'afecta_seguridad_social' => false,
                'afecta_parafiscales' => false,
                'orden_colilla' => 50,
                'visible_colilla' => false,
                'agrupador' => 'APORTES_EMPLEADOR',
            ],
            [
                'codigo' => 'PENSION_PATR',
                'nombre' => 'Pensión Empleador',
                'descripcion' => 'Aporte AFP empleador - 12%',
                'clasificacion' => 'no_imputable',
                'tipo' => 'calculado',
                'porcentaje' => 12.00,
                'base_salarial' => true,
                'afecta_prestaciones' => false,
                'afecta_seguridad_social' => false,
                'afecta_parafiscales' => false,
                'orden_colilla' => 51,
                'visible_colilla' => false,
                'agrupador' => 'APORTES_EMPLEADOR',
            ],
            [
                'codigo' => 'ARL',
                'nombre' => 'ARL - Seguro Riesgos Laborales',
                'descripcion' => 'Aporte ARL empleador - 5.2%',
                'clasificacion' => 'no_imputable',
                'tipo' => 'calculado',
                'porcentaje' => 5.20,
                'base_salarial' => true,
                'afecta_prestaciones' => false,
                'afecta_seguridad_social' => false,
                'afecta_parafiscales' => false,
                'orden_colilla' => 52,
                'visible_colilla' => false,
                'agrupador' => 'APORTES_EMPLEADOR',
            ],
            [
                'codigo' => 'CAJA_COMPENSACION',
                'nombre' => 'Caja de Compensación',
                'descripcion' => 'Aporte Caja Compensación - 4%',
                'clasificacion' => 'no_imputable',
                'tipo' => 'calculado',
                'porcentaje' => 4.00,
                'base_salarial' => true,
                'afecta_prestaciones' => false,
                'afecta_seguridad_social' => false,
                'afecta_parafiscales' => false,
                'orden_colilla' => 53,
                'visible_colilla' => false,
                'agrupador' => 'APORTES_EMPLEADOR',
            ],
            [
                'codigo' => 'SENA',
                'nombre' => 'SENA',
                'descripcion' => 'Aporte SENA - 2%',
                'clasificacion' => 'no_imputable',
                'tipo' => 'calculado',
                'porcentaje' => 2.00,
                'base_salarial' => true,
                'afecta_prestaciones' => false,
                'afecta_seguridad_social' => false,
                'afecta_parafiscales' => false,
                'orden_colilla' => 54,
                'visible_colilla' => false,
                'agrupador' => 'APORTES_EMPLEADOR',
            ],
            [
                'codigo' => 'ICBF',
                'nombre' => 'ICBF',
                'descripcion' => 'Aporte ICBF - 3%',
                'clasificacion' => 'no_imputable',
                'tipo' => 'calculado',
                'porcentaje' => 3.00,
                'base_salarial' => true,
                'afecta_prestaciones' => false,
                'afecta_seguridad_social' => false,
                'afecta_parafiscales' => false,
                'orden_colilla' => 55,
                'visible_colilla' => false,
                'agrupador' => 'APORTES_EMPLEADOR',
            ],

            // ═══════════════════════════════════════════════════════════
            // SECCIÓN 4: PROVISIONES (4 conceptos)
            // ═══════════════════════════════════════════════════════════

            [
                'codigo' => 'CESANTIAS',
                'nombre' => 'Cesantías',
                'descripcion' => 'Provisión de cesantías - 1/12 del total devengado',
                'clasificacion' => 'no_imputable',
                'tipo' => 'calculado',
                'porcentaje' => 8.33,
                'orden_colilla' => 60,
                'visible_colilla' => false,
                'agrupador' => 'PROVISIONES',
            ],
            [
                'codigo' => 'INTERESES_CESANTIAS',
                'nombre' => 'Intereses Cesantías',
                'descripcion' => 'Provisión intereses sobre cesantías - 12% anual',
                'clasificacion' => 'no_imputable',
                'tipo' => 'calculado',
                'porcentaje' => 1.00,
                'orden_colilla' => 61,
                'visible_colilla' => false,
                'agrupador' => 'PROVISIONES',
            ],
            [
                'codigo' => 'PRIMA',
                'nombre' => 'Prima de Servicios',
                'descripcion' => 'Provisión de prima - 1/12 del total devengado',
                'clasificacion' => 'no_imputable',
                'tipo' => 'calculado',
                'porcentaje' => 8.33,
                'orden_colilla' => 62,
                'visible_colilla' => false,
                'agrupador' => 'PROVISIONES',
            ],
            [
                'codigo' => 'VACACIONES',
                'nombre' => 'Vacaciones',
                'descripcion' => 'Provisión para vacaciones - 1/24 del total devengado',
                'clasificacion' => 'no_imputable',
                'tipo' => 'calculado',
                'porcentaje' => 4.17,
                'orden_colilla' => 63,
                'visible_colilla' => false,
                'agrupador' => 'PROVISIONES',
            ],
        ];
    }
}