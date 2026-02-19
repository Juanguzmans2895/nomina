<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Nomina\Models\CentroCosto;

class CentrosCostoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $centros = [
            // ═══ NIVEL 1 (PADRES) ═══
            [
                'codigo' => '001',
                'nombre' => 'Administración',
                'descripcion' => 'Centro de costo administrativo',
                'padre_id' => null,
                'activo' => true,
            ],
            [
                'codigo' => '002',
                'nombre' => 'Operaciones',
                'descripcion' => 'Centro de costo operativo',
                'padre_id' => null,
                'activo' => true,
            ],
            [
                'codigo' => '003',
                'nombre' => 'Comercial',
                'descripcion' => 'Centro de costo comercial',
                'padre_id' => null,
                'activo' => true,
            ],
            [
                'codigo' => '004',
                'nombre' => 'Tecnología',
                'descripcion' => 'Centro de costo de tecnología',
                'padre_id' => null,
                'activo' => true,
            ],
            
            // ═══ NIVEL 2 - HIJOS DE 001 (ADMINISTRACIÓN) ═══
            [
                'codigo' => '001001',
                'nombre' => 'Gerencia',
                'descripcion' => 'Gerencia general',
                'padre_id' => 1,
                'activo' => true,
            ],
            [
                'codigo' => '001002',
                'nombre' => 'Recursos Humanos',
                'descripcion' => 'Departamento de recursos humanos',
                'padre_id' => 1,
                'activo' => true,
            ],
            [
                'codigo' => '001003',
                'nombre' => 'Contabilidad',
                'descripcion' => 'Departamento de contabilidad',
                'padre_id' => 1,
                'activo' => true,
            ],
            [
                'codigo' => '001004',
                'nombre' => 'Sistemas',
                'descripcion' => 'Departamento de sistemas',
                'padre_id' => 1,
                'activo' => true,
            ],
            
            // ═══ NIVEL 2 - HIJOS DE 002 (OPERACIONES) ═══
            [
                'codigo' => '002001',
                'nombre' => 'Producción',
                'descripcion' => 'Área de producción',
                'padre_id' => 2,
                'activo' => true,
            ],
            [
                'codigo' => '002002',
                'nombre' => 'Logística',
                'descripcion' => 'Área de logística y almacén',
                'padre_id' => 2,
                'activo' => true,
            ],
            [
                'codigo' => '002003',
                'nombre' => 'Calidad',
                'descripcion' => 'Departamento de aseguramiento de calidad',
                'padre_id' => 2,
                'activo' => true,
            ],
            [
                'codigo' => '002004',
                'nombre' => 'Mantenimiento',
                'descripcion' => 'Departamento de mantenimiento',
                'padre_id' => 2,
                'activo' => true,
            ],
            
            // ═══ NIVEL 2 - HIJOS DE 003 (COMERCIAL) ═══
            [
                'codigo' => '003001',
                'nombre' => 'Ventas Nacionales',
                'descripcion' => 'Equipo de ventas nacionales',
                'padre_id' => 3,
                'activo' => true,
            ],
            [
                'codigo' => '003002',
                'nombre' => 'Ventas Internacionales',
                'descripcion' => 'Equipo de ventas internacionales',
                'padre_id' => 3,
                'activo' => true,
            ],
            [
                'codigo' => '003003',
                'nombre' => 'Marketing Digital',
                'descripcion' => 'Departamento de marketing digital',
                'padre_id' => 3,
                'activo' => true,
            ],
            [
                'codigo' => '003004',
                'nombre' => 'Servicio al Cliente',
                'descripcion' => 'Departamento de servicio al cliente',
                'padre_id' => 3,
                'activo' => true,
            ],
            
            // ═══ NIVEL 2 - HIJOS DE 004 (TECNOLOGÍA) ═══
            [
                'codigo' => '004001',
                'nombre' => 'Desarrollo',
                'descripcion' => 'Equipo de desarrollo de software',
                'padre_id' => 4,
                'activo' => true,
            ],
            [
                'codigo' => '004002',
                'nombre' => 'Infraestructura',
                'descripcion' => 'Área de infraestructura y servidores',
                'padre_id' => 4,
                'activo' => true,
            ],
            [
                'codigo' => '004003',
                'nombre' => 'Soporte Técnico',
                'descripcion' => 'Departamento de soporte técnico',
                'padre_id' => 4,
                'activo' => true,
            ],
            [
                'codigo' => '004004',
                'nombre' => 'Ciberseguridad',
                'descripcion' => 'Equipo de ciberseguridad',
                'padre_id' => 4,
                'activo' => true,
            ],
        ];

        foreach ($centros as $centro) {
            CentroCosto::create($centro);
        }
        
        $this->command->info('✓ ' . count($centros) . ' centros de costo creados exitosamente');
    }
}