<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Login del panel (Laravel UI).
 *
 * Incluye de serie: protección CSRF, regeneración de la sesión al entrar y bloqueo temporal
 * tras 5 intentos fallidos (trait ThrottlesLogins). Solo entra al panel el personal
 * (admin / moderador) con la cuenta activa.
 */
class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /** Se ejecuta DESPUÉS de validar correo y contraseña: aquí se revisan estado y rol. */
    protected function authenticated(Request $request, $user)
    {
        $motivo = match (true) {
            ! $user->activo        => 'Tu cuenta está desactivada. Contacta al administrador.',
            ! $user->esPersonal()  => 'Tu cuenta no tiene acceso al panel de administración. Usa la app móvil.',
            default                => null,
        };

        if ($motivo === null) {
            return null;   // continúa el flujo normal (redirección a /home)
        }

        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        throw ValidationException::withMessages([$this->username() => $motivo]);
    }
}
