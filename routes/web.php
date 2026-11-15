<?php

use App\Http\Controllers\Admin\ReporteUsuarioController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TipoEjercicioController;
use App\Http\Controllers\Admin\ActividadEntrenamientoController;
use App\Http\Controllers\Admin\ReporteActividadController;

/*
|--------------------------------------------------------------------------
| Rutas del panel web
|--------------------------------------------------------------------------
| Miembro 1: autenticación, usuarios y roles.
| Los demás módulos se agregan dentro del grupo ['auth', 'activo', 'es_moderador'],
| y las secciones solo para administradores con el middleware 'es_admin'.
*/

Route::get('/', fn () => redirect()->route(Auth::check() ? 'home' : 'login'));

// Login, logout y recuperación de contraseña (Laravel UI).
// El registro público está desactivado: las cuentas del panel las crea un administrador
// y los usuarios de la app móvil se registran por la API (POST /api/auth/registro).
Auth::routes(['register' => false]);

// Panel: requiere sesión, cuenta activa y ser admin o moderador
Route::middleware(['auth', 'activo', 'es_moderador'])->group(function () {

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::prefix('admin')->group(function () {

        // ---- Solo administradores (se registran primero para que "create" no choque con "{usuario}")
        Route::middleware('es_admin')->group(function () {
            Route::get('usuarios/reporte/pdf', [ReporteUsuarioController::class, 'pdf'])->name('usuarios.reporte.pdf');
            Route::get('usuarios/reporte/excel', [ReporteUsuarioController::class, 'excel'])->name('usuarios.reporte.excel');

            Route::resource('usuarios', UsuarioController::class)
                ->parameters(['usuarios' => 'usuario'])
                ->except(['index', 'show']);

            Route::patch('usuarios/{usuario}/toggle', [UsuarioController::class, 'toggle'])
                ->name('usuarios.toggle')
                ->whereNumber('usuario');

            // CRUD de tipos de ejercicios
            Route::resource('tiposEjercicio', TipoEjercicioController::class)->except(['show']);
            
            Route::get('actividades-entrenamientos', [ActividadEntrenamientoController::class, 'index'])->name('actividadesEntrenamientos.index');

            Route::get('actividades-entrenamientos/reporte/pdf', [ReporteActividadController::class, 'pdf'])->name('actividadesEntrenamientos.reporte.pdf');
        });

        // ---- Administradores y moderadores: solo consulta
        Route::resource('usuarios', UsuarioController::class)
            ->parameters(['usuarios' => 'usuario'])
            ->only(['index', 'show']);
    });
});

require __DIR__.'/planificacion_web.php';
require __DIR__.'/comunidad_web.php';
