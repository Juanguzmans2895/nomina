<?php

namespace App\Modules\Nomina\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class NominaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap del módulo de nómina
     */
    public function boot(): void
    {
        // Cargar migraciones del módulo
        $this->loadMigrationsFrom(__DIR__ . '/../../../database/migrations/nomina');

        // Cargar vistas del módulo
        $this->loadViewsFrom(resource_path('views/nomina'), 'nomina');

        // Publicar configuración
        $this->publishes([
            __DIR__ . '/../Config/nomina.php' => config_path('nomina.php'),
        ], 'nomina-config');

        // Publicar vistas
        $this->publishes([
            __DIR__ . '/../../../resources/views/nomina' => resource_path('views/vendor/nomina'),
        ], 'nomina-views');

        // Registrar rutas
        $this->registerRoutes();

        // Registrar comandos
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Aquí irán los comandos Artisan del módulo
            ]);
        }
    }

    /**
     * Registrar servicios del módulo
     */
    public function register(): void
    {
        // Merge configuración
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/nomina.php',
            'nomina'
        );

        // Registrar bindings de servicios
        $this->app->bind(
            \App\Modules\Nomina\Repositories\Interfaces\ConceptoRepositoryInterface::class,
            \App\Modules\Nomina\Repositories\ConceptoRepository::class
        );

        $this->app->bind(
            \App\Modules\Nomina\Repositories\Interfaces\ConceptoRepositoryInterface::class,
            \App\Modules\Nomina\Repositories\ConceptoRepository::class
        );

        $this->app->bind(
            \App\Modules\Nomina\Repositories\Interfaces\NominaRepositoryInterface::class,
            \App\Modules\Nomina\Repositories\NominaRepository::class
        );

        // Registrar servicios como singleton
        $this->app->singleton(
            \App\Modules\Nomina\Services\LiquidacionService::class
        );

        $this->app->singleton(
            \App\Modules\Nomina\Services\Calculo\CalculoSeguridadSocialService::class
        );

        $this->app->singleton(
            \App\Modules\Nomina\Services\Calculo\CalculoParafiscalesService::class
        );

        $this->app->singleton(
            \App\Modules\Nomina\Services\ContabilizacionService::class
        );
    }

    /**
     * Registrar las rutas del módulo
     */
    protected function registerRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__ . '/../Routes/web.php');

        Route::middleware(['web', 'auth'])
            ->prefix('nomina')
            ->name('nomina.')
            ->group(__DIR__ . '/../Routes/nomina.php');

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/nomina')
            ->name('api.nomina.')
            ->group(__DIR__ . '/../Routes/api.php');
    }
}
