<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Personal del panel: deja pasar a "moderador" y también a "admin".
 * Los usuarios de la app móvil (rol "usuario") no entran al panel.
 * Alias: es_moderador   →  Route::middleware(['auth', 'es_moderador'])
 */
class EsModerador
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Debes iniciar sesión.');
        }

        if (! $user->esPersonal()) {
            abort(403, 'No tienes permisos para acceder al panel.');
        }

        return $next($request);
    }
}
