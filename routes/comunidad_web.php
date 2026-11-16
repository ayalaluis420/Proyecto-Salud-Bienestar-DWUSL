<?php

use App\Http\Controllers\Admin\ConsejoController;
use App\Http\Controllers\Admin\ForoModeracionController;
use App\Http\Controllers\Admin\ReporteComunidadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas web de la comunidad (Miembro 4)
|--------------------------------------------------------------------------
| Moderación del foro: admin y moderador.
| Consejos y reporte de comunidad: solo admin.
*/
Route::middleware(['auth', 'activo', 'es_moderador'])->prefix('admin')->group(function () {
    // Foro: moderación
    Route::get('foro', [ForoModeracionController::class, 'index'])->name('foro.index');
    Route::patch('foro/comentarios/{comentario}/visibilidad', [ForoModeracionController::class, 'alternarComentario'])
        ->name('foro.comentarios.visibilidad')->whereNumber('comentario');
    Route::delete('foro/comentarios/{comentario}', [ForoModeracionController::class, 'eliminarComentario'])
        ->name('foro.comentarios.destroy')->whereNumber('comentario');
    Route::get('foro/{post}', [ForoModeracionController::class, 'show'])->name('foro.show')->whereNumber('post');
    Route::patch('foro/{post}/visibilidad', [ForoModeracionController::class, 'alternar'])
        ->name('foro.visibilidad')->whereNumber('post');
    Route::delete('foro/{post}', [ForoModeracionController::class, 'destroy'])->name('foro.destroy')->whereNumber('post');

    // Consejos y reporte: solo administradores
    Route::middleware('es_admin')->group(function () {
        Route::patch('consejos/{consejo}/publicacion', [ConsejoController::class, 'toggle'])
            ->name('consejos.toggle')->whereNumber('consejo');
        Route::resource('consejos', ConsejoController::class)->except(['show']);

        Route::get('reportes/comunidad', [ReporteComunidadController::class, 'index'])->name('reportes.comunidad');
        Route::get('reportes/comunidad/{formato}', [ReporteComunidadController::class, 'descargar'])
            ->whereIn('formato', ['pdf', 'excel'])->name('reportes.comunidad.descargar');
    });
});