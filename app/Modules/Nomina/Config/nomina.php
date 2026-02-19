<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración del Módulo de Nómina
    |--------------------------------------------------------------------------
    */

    'nombre' => 'Módulo de Nómina',
    'version' => '1.0.0',
    'descripcion' => 'Sistema de gestión de nómina para entidades públicas colombianas',

    /*
    |--------------------------------------------------------------------------
    | Salario Mínimo Legal Vigente (SMLV)
    |--------------------------------------------------------------------------
    */
    'smlv' => [
        'valor_actual' => env('SMLV_ACTUAL', 1300000),
        'anio' => 2024,
    ],

    /*
    |--------------------------------------------------------------------------
    | Unidad de Valor Tributario (UVT)
    |--------------------------------------------------------------------------
    */
    'uvt' => [
        'valor_actual' => env('UVT_ACTUAL', 47065),
        'anio' => 2024,
    ],

    /*
    |--------------------------------------------------------------------------
    | Topes Salariales (en SMLV)
    |--------------------------------------------------------------------------
    */
    'topes' => [
        'salud' => env('TOPE_SALUD', 13),
        'pension' => env('TOPE_PENSION', 25),
        'arl' => env('TOPE_ARL', 28),
    ],

    /*
    |--------------------------------------------------------------------------
    | Porcentajes de Seguridad Social
    |--------------------------------------------------------------------------
    */
    'seguridad_social' => [
        'pension' => [
            'empleado' => env('PENSION_EMPLEADO', 4.0),
            'empleador' => env('PENSION_EMPLEADOR', 12.0),
            'total' => env('PENSION_EMPLEADO', 4.0) + env('PENSION_EMPLEADOR', 12.0),
        ],
        'salud' => [
            'empleado' => env('SALUD_EMPLEADO', 4.0),
            'empleador' => env('SALUD_EMPLEADOR', 8.5),
            'total' => env('SALUD_EMPLEADO', 4.0) + env('SALUD_EMPLEADOR', 8.5),
        ],
        'arl' => [
            'empleado' => 0.0,
            'empleador' => env('ARL_EMPLEADOR', 0.522),
            'total' => env('ARL_EMPLEADOR', 0.522),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Aportes Parafiscales
    |--------------------------------------------------------------------------
    */
    'parafiscales' => [
        'sena' => env('SENA', 2.0),
        'icbf' => env('ICBF', 3.0),
        'caja_compensacion' => env('CAJA_COMPENSACION', 4.0),
        'exencion_salarios' => 10, // Salarios superiores a 10 SMLV están exentos
    ],

    /*
    |--------------------------------------------------------------------------
    | Provisiones Laborales
    |--------------------------------------------------------------------------
    */
    'provisiones' => [
        'cesantias' => [
            'porcentaje' => 8.33, // Mensual
            'intereses' => 12.0, // Anual
        ],
        'prima' => [
            'porcentaje' => 8.33, // Mensual
            'periodos' => [
                'junio' => ['inicio' => '01-01', 'fin' => '06-30'],
                'diciembre' => ['inicio' => '07-01', 'fin' => '12-31'],
            ],
        ],
        'vacaciones' => [
            'porcentaje' => 4.17, // Mensual (15 días por año)
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Retención en la Fuente
    |--------------------------------------------------------------------------
    */
    'retencion' => [
        'tabla_uvt' => [
            // [limite_inferior_uvt, limite_superior_uvt, tarifa_marginal, retencion_desde_uvt]
            [0, 95, 0, 0],
            [95, 150, 19, 0],
            [150, 360, 28, 10],
            [360, 640, 33, 69],
            [640, 945, 35, 162],
            [945, 2300, 37, 268],
            [945, 'INF', 39, 770],
        ],
        'deducciones' => [
            'salud_obligatoria' => true,
            'pension_obligatoria' => true,
            'dependientes' => 32, // UVT por dependiente
            'interes_vivienda' => 1200, // Límite en UVT
            'medicina_prepagada' => 192, // Límite en UVT
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tipos de Nómina
    |--------------------------------------------------------------------------
    */
    'tipos_nomina' => [
        'empleados' => 'Nómina General de Empleados',
        'contratistas' => 'Nómina de Contratistas',
        'prima' => 'Nómina de Prima',
        'vacaciones' => 'Nómina de Vacaciones',
        'bonificaciones' => 'Bonificaciones',
        'liquidacion' => 'Liquidación de Contrato',
    ],

    /*
    |--------------------------------------------------------------------------
    | Periodicidad de Nómina
    |--------------------------------------------------------------------------
    */
    'periodicidad' => [
        'quincenal' => 'Quincenal',
        'mensual' => 'Mensual',
        'bimensual' => 'Bimensual',
    ],

    /*
    |--------------------------------------------------------------------------
    | Estados de Nómina
    |--------------------------------------------------------------------------
    */
    'estados_nomina' => [
        'borrador' => 'Borrador',
        'prenomina' => 'Pre-nómina',
        'aprobada' => 'Aprobada',
        'causada' => 'Causada',
        'contabilizada' => 'Contabilizada',
        'pagada' => 'Pagada',
        'anulada' => 'Anulada',
    ],

    /*
    |--------------------------------------------------------------------------
    | Clasificación de Conceptos
    |--------------------------------------------------------------------------
    */
    'clasificacion_conceptos' => [
        'devengado' => 'Devengado',
        'deducido' => 'Deducido',
        'no_imputable' => 'No Imputable',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tipo de Conceptos
    |--------------------------------------------------------------------------
    */
    'tipo_conceptos' => [
        'fijo' => 'Fijo',
        'novedad' => 'Novedad',
        'calculado' => 'Calculado',
    ],

    /*
    |--------------------------------------------------------------------------
    | Integración con otros módulos
    |--------------------------------------------------------------------------
    */
    'integracion' => [
        'presupuesto' => [
            'enabled' => env('PRESUPUESTO_ENABLED', true),
            'api_url' => env('PRESUPUESTO_API_URL', 'http://localhost:8001/api'),
            'validar_disponibilidad' => true,
        ],
        'contabilidad' => [
            'enabled' => env('CONTABILIDAD_ENABLED', true),
            'api_url' => env('CONTABILIDAD_API_URL', 'http://localhost:8002/api'),
            'auto_contabilizar' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Reportes
    |--------------------------------------------------------------------------
    */
    'reportes' => [
        'logo_path' => 'images/logo.png',
        'razon_social' => 'Entidad Pública',
        'nit' => '000000000-0',
        'direccion' => 'Calle XX # XX-XX',
        'ciudad' => 'Bogotá D.C.',
        'telefono' => '(601) 000-0000',
        'formato_fecha' => 'd/m/Y',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Archivos PILA
    |--------------------------------------------------------------------------
    */
    'pila' => [
        'operador_informacion' => env('PILA_OPERADOR_INFORMACION', ''),
        'codigo_arl' => env('PILA_CODIGO_ARL', ''),
        'version' => '5',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Permisos
    |--------------------------------------------------------------------------
    */
    'permisos' => [
        'empleados' => ['ver', 'crear', 'editar', 'eliminar'],
        'conceptos' => ['ver', 'crear', 'editar', 'eliminar'],
        'nominas' => ['ver', 'crear', 'editar', 'aprobar', 'causar', 'anular'],
        'contratos' => ['ver', 'crear', 'editar', 'eliminar'],
        'reportes' => ['ver', 'exportar'],
        'configuracion' => ['ver', 'editar'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Auditoría
    |--------------------------------------------------------------------------
    */
    'auditoria' => [
        'enabled' => true,
        'log_queries' => false,
        'log_changes' => true,
    ],
];
