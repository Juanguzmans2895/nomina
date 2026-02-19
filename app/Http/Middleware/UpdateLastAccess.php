<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Solo actualizar si han pasado más de 5 minutos desde la última actualización
            if (!$user->ultimo_acceso || $user->ultimo_acceso->lt(now()->subMinutes(5))) {
                $user->timestamps = false; // No actualizar updated_at
                $user->ultimo_acceso = now();
                $user->save();
                $user->timestamps = true;
            }
        }

        return $next($request);
    }
}