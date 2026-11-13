<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Recuperación de contraseña (Laravel UI).
 * Flujo: /password/reset → correo con enlace firmado (token de un solo uso, caduca en 60 min)
 *        → /password/reset/{token} → nueva contraseña (cumple Password::defaults()).
 *
 * En desarrollo MAIL_MAILER=log: el correo con el enlace queda en storage/logs/laravel.log.
 */
class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected $redirectTo = '/login';

    /** Cambia la contraseña pero NO inicia sesión: la persona debe entrar con la nueva clave. */
    protected function resetPassword($user, $password)
    {
        $this->setUserPassword($user, $password);
        $user->setRememberToken(Str::random(60));
        $user->save();

        $user->tokens()->delete();   // por seguridad se cierran las sesiones de la app móvil

        event(new PasswordReset($user));
    }

    protected function sendResetResponse(Request $request, $response)
    {
        return redirect()->route('login')->with('status', trans($response));
    }
}
