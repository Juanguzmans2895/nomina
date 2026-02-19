<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Nomina\Http\Controllers\Api\EmpleadoController;
use App\Modules\Nomina\Http\Controllers\Api\ConceptoController;
use App\Modules\Nomina\Http\Controllers\Api\NominaController;
use App\Modules\Nomina\Http\Controllers\Api\ContratoController;
use App\Modules\Nomina\Http\Controllers\Api\ReporteController;

/*
|--------------------------------------------------------------------------
| Rutas API del Módulo de Nómina
|--------------------------------------------------------------------------
*/

// Empleados
Route::apiResource('empleados', EmpleadoController::class);
Route::prefix('empleados')->name('empleados.')->group(function () {
    Route::get('{empleado}/centros-costo', [EmpleadoController::class, 'centrosCosto'])
        ->name('centros-costo');
    Route::post('{empleado}/asignar-centro-costo', [EmpleadoController::class, 'asignarCentroCosto'])
        ->name('asignar-centro-costo');
    Route::get('{empleado}/conceptos-fijos', [EmpleadoController::class, 'conceptosFijos'])
        ->name('conceptos-fijos');
    Route::get('{empleado}/nominas', [EmpleadoController::class, 'nominas'])
        ->name('nominas');
});

// Conceptos
Route::apiResource('conceptos', ConceptoController::class);
Route::prefix('conceptos')->name('conceptos.')->group(function () {
    Route::get('clasificacion/{clasificacion}', [ConceptoController::class, 'porClasificacion'])
        ->name('por-clasificacion');
    Route::get('tipo/{tipo}', [ConceptoController::class, 'porTipo'])
        ->name('por-tipo');
});

// Centros de Costo
Route::apiResource('centros-costo', CentroCostoController::class);

// Nóminas
Route::apiResource('nominas', NominaController::class);
Route::prefix('nominas')->name('nominas.')->group(function () {
    Route::post('{nomina}/prenomina', [NominaController::class, 'generarPrenomina'])
        ->name('prenomina');
    Route::post('{nomina}/aprobar', [NominaController::class, 'aprobar'])
        ->name('aprobar');
    Route::post('{nomina}/causar', [NominaController::class, 'causar'])
        ->name('causar');
    Route::post('{nomina}/contabilizar', [NominaController::class, 'contabilizar'])
        ->name('contabilizar');
    Route::post('{nomina}/anular', [NominaController::class, 'anular'])
        ->name('anular');
    Route::get('{nomina}/detalles', [NominaController::class, 'detalles'])
        ->name('detalles');
    Route::post('{nomina}/cargar-novedades', [NominaController::class, 'cargarNovedades'])
        ->name('cargar-novedades');
});

// Contratos
Route::apiResource('contratos', ContratoController::class);
Route::prefix('contratos')->name('contratos.')->group(function () {
    Route::get('{contrato}/pagos', [ContratoController::class, 'pagos'])
        ->name('pagos');
    Route::post('{contrato}/registrar-pago', [ContratoController::class, 'registrarPago'])
        ->name('registrar-pago');
});

// Provisiones
Route::prefix('provisiones')->name('provisiones.')->group(function () {
    Route::get('/', [ProvisionController::class, 'index'])->name('index');
    Route::post('/calcular', [ProvisionController::class, 'calcular'])->name('calcular');
    Route::get('/empleado/{empleado}', [ProvisionController::class, 'porEmpleado'])
        ->name('por-empleado');
    Route::get('/periodo/{periodo}', [ProvisionController::class, 'porPeriodo'])
        ->name('por-periodo');
});

// Reportes
Route::prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/nomina-detallada', [ReporteController::class, 'nominaDetallada'])
        ->name('nomina-detallada');
    Route::get('/desprendibles/{nomina}', [ReporteController::class, 'desprendibles'])
        ->name('desprendibles');
    Route::get('/desprendible/{nomina}/{empleado}', [ReporteController::class, 'desprendible'])
        ->name('desprendible');
    Route::get('/consolidado-entidades', [ReporteController::class, 'consolidadoEntidades'])
        ->name('consolidado-entidades');
    Route::get('/acumulados/{empleado}', [ReporteController::class, 'acumulados'])
        ->name('acumulados');
    Route::get('/certificado-laboral/{empleado}', [ReporteController::class, 'certificadoLaboral'])
        ->name('certificado-laboral');
    Route::get('/certificado-ingresos/{empleado}', [ReporteController::class, 'certificadoIngresos'])
        ->name('certificado-ingresos');
    Route::post('/exportar', [ReporteController::class, 'exportar'])
        ->name('exportar');
});

// Integración con Presupuesto
Route::prefix('integracion/presupuesto')->name('integracion.presupuesto.')->group(function () {
    Route::post('/validar-disponibilidad', [IntegracionController::class, 'validarDisponibilidad'])
        ->name('validar-disponibilidad');
    Route::post('/afectar', [IntegracionController::class, 'afectarPresupuesto'])
        ->name('afectar');
});

// Integración con Contabilidad
Route::prefix('integracion/contabilidad')->name('integracion.contabilidad.')->group(function () {
    Route::post('/generar-asientos', [IntegracionController::class, 'generarAsientos'])
        ->name('generar-asientos');
    Route::post('/contabilizar-nomina', [IntegracionController::class, 'contabilizarNomina'])
        ->name('contabilizar-nomina');
});

// Utilidades
Route::prefix('utilidades')->name('utilidades.')->group(function () {
    Route::get('/smlv', function () {
        return response()->json(['smlv' => config('nomina.smlv.valor_actual')]);
    })->name('smlv');
    
    Route::get('/uvt', function () {
        return response()->json(['uvt' => config('nomina.uvt.valor_actual')]);
    })->name('uvt');
    
    Route::get('/parametros', function () {
        return response()->json([
            'smlv' => config('nomina.smlv'),
            'uvt' => config('nomina.uvt'),
            'topes' => config('nomina.topes'),
            'seguridad_social' => config('nomina.seguridad_social'),
            'parafiscales' => config('nomina.parafiscales'),
        ]);
    })->name('parametros');
});
