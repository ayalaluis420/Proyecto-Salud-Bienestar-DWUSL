<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Solo deja pasar a usuarios con rol "admin".
 * Alias: es_admin   →  Route::middleware(['auth', 'es_admin'])
 */
class EsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Debes iniciar sesión.');
        }

        if (! $user->esAdmin()) {
            abort(403, 'Esta sección es solo para administradores.');
        }

        return $next($request);
    }
}
