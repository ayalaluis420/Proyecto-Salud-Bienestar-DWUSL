<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Si un usuario es desactivado mientras tiene sesión (panel) o token (API),
 * se le corta el acceso en la siguiente petición.
 * Alias: activo
 */
class UsuarioActivo
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->activo) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $token = $user->currentAccessToken();
                if ($token && method_exists($token, 'delete')) {
                    $token->delete();                 // revoca el token usado
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Tu cuenta está desactivada. Contacta al administrador.',
                ], 403);
            }

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta está desactivada. Contacta al administrador.']);
        }

        return $next($request);
    }
}
