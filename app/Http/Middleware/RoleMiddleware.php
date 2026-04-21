<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Uso:
     *   ->middleware('role:admin')
     *   ->middleware('role:admin,editor')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Si no hay usuario logueado, no puede pasar
        if (!$user) {
            abort(403);
        }

        // Si no se especificaron roles, dejamos pasar (por seguridad del developer: no bloquea)
        if (count($roles) === 0) {
            return $next($request);
        }

        // Normalizamos roles (por si vienen con espacios)
        $roles = array_map(fn ($r) => trim(strtolower($r)), $roles);
        $userRole = strtolower((string) $user->role);

        if (!in_array($userRole, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}