<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Web del Módulo de Nómina
|--------------------------------------------------------------------------
*/

Route::prefix('nomina')
    ->name('nomina.')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        
        // Dashboard
        Route::get('/', function () {
            return view('nomina.dashboard');
        })->name('dashboard');

        // Empleados
        Route::prefix('empleados')->name('empleados.')->group(function () {
            Route::get('/', function () {
                return view('nomina.empleados.index');
            })->name('index');
        });

        // Conceptos
        Route::prefix('conceptos')->name('conceptos.')->group(function () {
            Route::get('/', function () {
                return view('nomina.conceptos.index');
            })->name('index');
        });

        // Centros de Costo
        Route::prefix('centros-costo')->name('centros-costo.')->group(function () {
            Route::get('/', function () {
                return view('nomina.centros-costo.index');
            })->name('index');
        });

        // Nóminas
        Route::prefix('nominas')->name('nominas.')->group(function () {
            Route::get('/', function () {
                return view('nomina.nominas.index');
            })->name('index');
            Route::get('/crear', function () {
                return view('nomina.nominas.crear');
            })->name('crear');
            Route::get('/{nomina}', function () {
                return view('nomina.nominas.detalle');
            })->name('detalle');
        });

        // Contratos
        Route::prefix('contratos')->name('contratos.')->group(function () {
            Route::get('/', function () {
                return view('nomina.contratos.index');
            })->name('index');
        });

        // Provisiones
        Route::prefix('provisiones')->name('provisiones.')->group(function () {
            Route::get('/', function () {
                return view('nomina.provisiones.index');
            })->name('index');
        });

        // Reportes
        Route::prefix('reportes')->name('reportes.')->group(function () {
            Route::get('/', function () {
                return view('nomina.reportes.index');
            })->name('index');
            
            Route::get('/nomina-detallada', function () {
                return view('nomina.reportes.nomina-detallada');
            })->name('nomina-detallada');
            
            Route::get('/desprendibles', function () {
                return view('nomina.reportes.desprendibles');
            })->name('desprendibles');
            
            Route::get('/consolidados', function () {
                return view('nomina.reportes.consolidados');
            })->name('consolidados');
            
            Route::get('/acumulados', function () {
                return view('nomina.reportes.acumulados');
            })->name('acumulados');
        });

        // Configuración
        Route::prefix('configuracion')->name('configuracion.')->group(function () {
            Route::get('/', function () {
                return view('nomina.configuracion.index');
            })->name('index');
        });
    });
