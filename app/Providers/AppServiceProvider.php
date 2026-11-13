<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Política de contraseñas segura, única para panel, API y recuperación de contraseña:
        // mínimo 8 caracteres, mayúsculas y minúsculas, números y símbolos.
        // En producción además se comprueba que no esté en filtraciones conocidas.
        Password::defaults(function () {
            $regla = Password::min(8)->letters()->mixedCase()->numbers()->symbols();

            return $this->app->isProduction() ? $regla->uncompromised() : $regla;
        });
    }
}
