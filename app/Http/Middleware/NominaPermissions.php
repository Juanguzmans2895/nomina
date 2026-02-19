<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NominaPermissions
{
    /**
     * Permisos del sistema de nómina
     */
    protected $permissions = [
        // Dashboard
        'nomina.dashboard' => 'Ver dashboard de nómina',
        
        // Empleados
        'nomina.empleados.ver' => 'Ver empleados',
        'nomina.empleados.crear' => 'Crear empleados',
        'nomina.empleados.editar' => 'Editar empleados',
        'nomina.empleados.eliminar' => 'Eliminar empleados',
        'nomina.empleados.asignar-centros' => 'Asignar centros de costo',
        'nomina.empleados.asignar-conceptos' => 'Asignar conceptos fijos',
        
        // Conceptos
        'nomina.conceptos.ver' => 'Ver conceptos',
        'nomina.conceptos.crear' => 'Crear conceptos',
        'nomina.conceptos.editar' => 'Editar conceptos',
        'nomina.conceptos.eliminar' => 'Eliminar conceptos',
        
        // Centros de Costo
        'nomina.centros-costo.ver' => 'Ver centros de costo',
        'nomina.centros-costo.crear' => 'Crear centros de costo',
        'nomina.centros-costo.editar' => 'Editar centros de costo',
        'nomina.centros-costo.eliminar' => 'Eliminar centros de costo',
        
        // Contratos
        'nomina.contratos.ver' => 'Ver contratos',
        'nomina.contratos.crear' => 'Crear contratos',
        'nomina.contratos.editar' => 'Editar contratos',
        'nomina.contratos.eliminar' => 'Eliminar contratos',
        'nomina.contratos.aprobar' => 'Aprobar contratos',
        'nomina.contratos.pagar' => 'Registrar pagos',
        
        // Nóminas
        'nomina.nominas.ver' => 'Ver nóminas',
        'nomina.nominas.liquidar' => 'Liquidar nóminas',
        'nomina.nominas.aprobar' => 'Aprobar nóminas',
        'nomina.nominas.contabilizar' => 'Contabilizar nóminas',
        'nomina.nominas.pagar' => 'Marcar como pagada',
        'nomina.nominas.anular' => 'Anular nóminas',
        
        // Novedades
        'nomina.novedades.ver' => 'Ver novedades',
        'nomina.novedades.crear' => 'Crear novedades',
        'nomina.novedades.editar' => 'Editar novedades',
        'nomina.novedades.eliminar' => 'Eliminar novedades',
        'nomina.novedades.aprobar' => 'Aprobar novedades',
        'nomina.novedades.importar' => 'Importar novedades masivas',
        
        // Provisiones
        'nomina.provisiones.ver' => 'Ver provisiones',
        'nomina.provisiones.pagar' => 'Pagar provisiones',
        'nomina.provisiones.liquidar' => 'Liquidar provisiones',
        
        // Contabilización
        'nomina.contabilidad.ver' => 'Ver asientos contables',
        'nomina.contabilidad.aprobar' => 'Aprobar asientos',
        'nomina.contabilidad.contabilizar' => 'Contabilizar asientos',
        'nomina.contabilidad.anular' => 'Anular asientos',
        
        // Reportes
        'nomina.reportes.desprendibles' => 'Generar desprendibles',
        'nomina.reportes.certificados' => 'Generar certificados',
        'nomina.reportes.consolidados' => 'Generar consolidados',
        'nomina.reportes.pila' => 'Generar archivo PILA',
        'nomina.reportes.exportar' => 'Exportar reportes',
        
        // Configuración
        'nomina.configuracion.ver' => 'Ver configuración',
        'nomina.configuracion.editar' => 'Editar configuración',
        
        // Super Admin
        'nomina.admin' => 'Administrador total de nómina',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Si el usuario no está autenticado, redirigir al login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Super admin siempre tiene acceso
        if ($this->isSuperAdmin($user)) {
            return $next($request);
        }

        // Verificar si tiene el permiso específico
        if (!$this->hasPermission($user, $permission)) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        return $next($request);
    }

    /**
     * Verificar si el usuario es super admin
     */
    protected function isSuperAdmin($user): bool
    {
        // Opción 1: Campo en la tabla users
        if (isset($user->is_super_admin) && $user->is_super_admin) {
            return true;
        }

        // Opción 2: Rol específico
        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return true;
        }

        // Opción 3: Permiso específico
        if ($this->hasPermission($user, 'nomina.admin')) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    protected function hasPermission($user, string $permission): bool
    {
        // Si usas Spatie Laravel Permission
        if (method_exists($user, 'hasPermissionTo')) {
            return $user->hasPermissionTo($permission);
        }

        // Si usas Laravel Jetstream/Fortify con permissions en array
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission($permission);
        }

        // Si usas permisos personalizados
        if (method_exists($user, 'permissions')) {
            return in_array($permission, $user->permissions());
        }

        // Si tienes una tabla pivot user_permissions
        if ($user->permissions()->where('name', $permission)->exists()) {
            return true;
        }

        // Por defecto, denegar acceso
        return false;
    }

    /**
     * Obtener todos los permisos disponibles
     */
    public static function getAvailablePermissions(): array
    {
        return (new self())->permissions;
    }

    /**
     * Verificar múltiples permisos (requiere al menos uno)
     */
    public function requiresAnyPermission($user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($user, $permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verificar múltiples permisos (requiere todos)
     */
    public function requiresAllPermissions($user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($user, $permission)) {
                return false;
            }
        }
        return true;
    }
}