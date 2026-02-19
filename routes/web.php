<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Nomina\NominaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Nomina\ReportesController;
use App\Http\Controllers\Nomina\EmpleadoController;
use App\Http\Controllers\Nomina\CentroCostoController;
use App\Http\Controllers\Nomina\ConceptoController;
use App\Http\Controllers\Nomina\PeriodoController;
use App\Http\Controllers\Nomina\ContratoController;
use App\Http\Controllers\Nomina\ConfiguracionController;

/*
|--------------------------------------------------------------------------
| RUTAS PRINCIPALES - SOLUCIÓN ERROR 404
|--------------------------------------------------------------------------
*/

// RUTA RAÍZ: http://nomina.test/
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// DASHBOARD PRINCIPAL: http://nomina.test/dashboard
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    ->get('/dashboard', function () {
        return redirect()->route('nomina.dashboard');
    })->name('dashboard');

/*
|--------------------------------------------------------------------------
| RUTAS DE ADMINISTRACIÓN DE USUARIOS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('admin/usuarios')->name('admin.usuarios.')->group(function () {
    Route::get('/',                     [UserController::class, 'index'])         ->name('index');
    Route::get('/crear',                [UserController::class, 'create'])        ->name('create');
    Route::post('/',                    [UserController::class, 'store'])         ->name('store');
    Route::get('/{usuario}/editar',     [UserController::class, 'edit'])          ->name('edit');
    Route::put('/{usuario}',            [UserController::class, 'update'])        ->name('update');
    Route::delete('/{usuario}',         [UserController::class, 'destroy'])       ->name('destroy');
    Route::post('/{usuario}/toggle',    [UserController::class, 'toggleActivo'])  ->name('toggle');
    Route::post('/{id}/restore',        [UserController::class, 'restore'])       ->name('restore');
    Route::post('/{usuario}/password',  [UserController::class, 'resetPassword']) ->name('reset-password');
});

/*
|--------------------------------------------------------------------------
| RUTAS DE NÓMINA
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('nomina')->name('nomina.')->group(function () {
    
    // Dashboard
    Route::get('/', [NominaController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard-nomina', [NominaController::class, 'dashboardNomina'])->name('dashboard-nomina');

    // EMPLEADOS
    Route::prefix('empleados')->name('empleados.')->group(function () {
        Route::get('/', [EmpleadoController::class, 'index'])->name('index');
        Route::get('/crear', [EmpleadoController::class, 'create'])->name('crear');
        Route::post('/', [EmpleadoController::class, 'store'])->name('store');
        Route::get('/{empleado}/editar', [EmpleadoController::class, 'edit'])->name('editar');
        Route::put('/{empleado}', [EmpleadoController::class, 'update'])->name('update');
        Route::delete('/{empleado}', [EmpleadoController::class, 'destroy'])->name('destroy');
        Route::get('/{empleado}/centros-costo', [EmpleadoController::class, 'centrosCosto'])->name('centros-costo');
        Route::post('/{empleado}/centros-costo', [EmpleadoController::class, 'guardarCentrosCosto'])->name('guardar-centros-costo');
        Route::get('/{empleado}/conceptos-fijos', [EmpleadoController::class, 'conceptosFijos'])->name('conceptos-fijos');
        Route::post('/{empleado}/conceptos-fijos', [EmpleadoController::class, 'guardarConceptosFijos'])->name('guardar-conceptos-fijos');
    });

    Route::prefix('asientos')->name('asientos.')->group(function () {
        Route::get('/', [NominaController::class, 'asientosContables'])->name('index');
        Route::get('/exportar', [NominaController::class, 'exportarAsientos'])->name('exportar');
        Route::get('/{id}', [NominaController::class, 'detalleAsiento'])->name('detalle');
        Route::post('/{id}/aprobar', [NominaController::class, 'aprobarAsiento'])->name('aprobar');
        Route::post('/{id}/contabilizar', [NominaController::class, 'contabilizarAsiento'])->name('contabilizar');
        Route::post('/{id}/anular', [NominaController::class, 'anularAsiento'])->name('anular');
    });
    
    // CONCEPTOS
    Route::prefix('conceptos')->name('conceptos.')->group(function () {
        Route::get('/', [ConceptoController::class, 'index'])->name('index');
        Route::get('/create', [ConceptoController::class, 'create'])->name('create');
        Route::post('/', [ConceptoController::class, 'store'])->name('store');
        Route::get('/{concepto}/edit', [ConceptoController::class, 'edit'])->name('edit');
        Route::put('/{concepto}', [ConceptoController::class, 'update'])->name('update');
        Route::delete('/{concepto}', [ConceptoController::class, 'destroy'])->name('destroy');
        Route::get('/buscar', [ConceptoController::class, 'buscar'])->name('buscar');
        Route::get('/activos', [ConceptoController::class, 'activos'])->name('activos');
    });

    // CENTROS DE COSTO
    Route::resource('centros-costo', CentroCostoController::class);
    
    // CONTRATOS
    Route::prefix('contratos')->name('contratos.')->group(function () {
        // CRUD básico
        Route::get('/', [ContratoController::class, 'index'])->name('index');
        Route::get('/crear', [ContratoController::class, 'create'])->name('create');
        Route::post('/', [ContratoController::class, 'store'])->name('store');
        Route::get('/{contrato}', [ContratoController::class, 'show'])->name('show');
        Route::get('/{contrato}/editar', [ContratoController::class, 'edit'])->name('edit');
        Route::put('/{contrato}', [ContratoController::class, 'update'])->name('update');
        Route::delete('/{contrato}', [ContratoController::class, 'destroy'])->name('destroy');
        
        // PAGOS
        Route::get('/{contrato}/pagos', [ContratoController::class, 'pagos'])->name('pagos');
        
        // Nested routes para pagos
        Route::prefix('{contrato}/pagos')->name('pagos.')->group(function () {
            Route::get('/crear', [ContratoController::class, 'createPago'])->name('create');
            Route::post('/', [ContratoController::class, 'storePago'])->name('store');
            Route::post('/{pago}/aprobar', [ContratoController::class, 'aprobarPago'])->name('aprobar');
            Route::post('/{pago}/pagar', [ContratoController::class, 'marcarPagado'])->name('pagar');
            Route::get('/{pago}/editar', [ContratoController::class, 'editPago'])->name('edit');
            Route::put('/{pago}', [ContratoController::class, 'updatePago'])->name('update');
            Route::delete('/{pago}', [ContratoController::class, 'destroyPago'])->name('destroy');
        });
    });
    
    // NÓMINAS
    Route::prefix('nominas')->name('nominas.')->group(function () {
        // Wizard de liquidación
        Route::get('/liquidar', [NominaController::class, 'liquidar'])->name('liquidar');
        Route::post('/wizard/guardar', [NominaController::class, 'guardarPasoWizard'])->name('wizard.guardar');
        Route::post('/procesar', [NominaController::class, 'procesar'])->name('procesar');
        
        // Gestión de nóminas
        Route::get('/historial', [NominaController::class, 'historial'])->name('historial');
        Route::get('/{nomina}/detalles', [NominaController::class, 'detalles'])->name('detalles');
        Route::get('/{nomina}/editar', [NominaController::class, 'editar'])->name('editar');
        Route::put('/{nomina}', [NominaController::class, 'actualizar'])->name('actualizar');
        Route::delete('/{nomina}', [NominaController::class, 'eliminar'])->name('eliminar');
        
        // Acciones de estado
        Route::post('/{nomina}/aprobar', [NominaController::class, 'aprobar'])->name('aprobar');
        Route::post('/{nomina}/pagar', [NominaController::class, 'pagar'])->name('pagar');
        Route::post('/{nomina}/cerrar', [NominaController::class, 'cerrar'])->name('cerrar');
        Route::post('/{nomina}/anular', [NominaController::class, 'anular'])->name('anular');
    });
    
    // NOVEDADES
    Route::prefix('novedades')->name('novedades.')->group(function () {
        Route::get('/', [NominaController::class, 'novedades'])->name('index');
        Route::get('/crear', [NominaController::class, 'crearNovedad'])->name('crear');
        Route::post('/', [NominaController::class, 'guardarNovedad'])->name('store');
        Route::get('/{novedad}/editar', [NominaController::class, 'editarNovedad'])->name('editar');
        Route::put('/{novedad}', [NominaController::class, 'actualizarNovedad'])->name('update');
        Route::delete('/{novedad}', [NominaController::class, 'eliminarNovedad'])->name('destroy');
        
        // Importación
        Route::get('/importar', [NominaController::class, 'importarNovedades'])->name('importar');
        Route::post('/importar', [NominaController::class, 'procesarImportacion'])->name('procesar-importacion');
        Route::post('/importar/confirmar', [NominaController::class, 'confirmarImportacionNovedades'])->name('confirmar-importacion');
        Route::get('/plantilla', [NominaController::class, 'descargarPlantilla'])->name('plantilla');
    });
    
    // PROVISIONES
    Route::prefix('provisiones')->name('provisiones.')->group(function () {
        Route::get('/', [NominaController::class, 'provisiones'])->name('index');
        Route::get('/asientos', [NominaController::class, 'asientosContables'])->name('asientos');
        Route::post('/generar', [NominaController::class, 'generarProvisiones'])->name('generar');
    });
    
    // REPORTES
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', [ReportesController::class, 'index'])->name('index');
        Route::get('/desprendible/{detalle}', [ReportesController::class, 'desprendible'])->name('desprendible');
        Route::get('/desprendibles-masivo/{nomina}', [ReportesController::class, 'desprendiblesMasivo'])->name('desprendibles-masivo');
        Route::get('/certificado-laboral/{empleado}', [ReportesController::class, 'certificadoLaboral'])->name('certificado-laboral');
        Route::get('/certificado-ingresos/{empleado}/{anio}', [ReportesController::class, 'certificadoIngresos'])->name('certificado-ingresos');
        Route::get('/certificado-cesantias/{empleado}', [ReportesController::class, 'certificadoCesantias'])->name('certificado-cesantias');
        Route::get('/consolidado/{nomina}', [ReportesController::class, 'consolidado'])->name('consolidado');
        Route::get('/consolidado-seguridad-social/{nomina}', [ReportesController::class, 'consolidadoSeguridadSocial'])->name('consolidado-seguridad-social');
        Route::get('/reporte-ejecutivo/{periodo}', [ReportesController::class, 'reporteEjecutivo'])->name('reporte-ejecutivo');
        Route::get('/pila/{nomina}', [ReportesController::class, 'archivoPILA'])->name('pila');
        Route::get('/excel/nomina/{nomina}', [ReportesController::class, 'exportarNominaExcel'])->name('excel-nomina');
        Route::get('/excel/provisiones', [ReportesController::class, 'exportarProvisionesExcel'])->name('excel-provisiones');
    });
    
    // API (Para AJAX)
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/empleados/buscar', [EmpleadoController::class, 'buscar'])->name('empleados.buscar');
        Route::get('/empleados/{empleado}/datos', [EmpleadoController::class, 'obtenerDatos'])->name('empleados.datos');
        Route::get('/periodos/abiertos', [PeriodoController::class, 'abiertos'])->name('periodos.abiertos');
        Route::get('/periodos/{periodo}/validar', [PeriodoController::class, 'validar'])->name('periodos.validar');
    });
});

/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('nomina/configuracion')->name('nomina.configuracion.')->group(function () {
    Route::get('/', [ConfiguracionController::class, 'index'])->name('index');
    Route::post('/actualizar', [ConfiguracionController::class, 'actualizar'])->name('actualizar');
    Route::post('/importar', [ConfiguracionController::class, 'importar'])->name('importar');
    Route::get('/exportar', [ConfiguracionController::class, 'exportar'])->name('exportar');
});