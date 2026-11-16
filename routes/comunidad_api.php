<?php

use App\Http\Controllers\Api\ConsejoController;
use App\Http\Controllers\Api\ForoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API de comunidad (Miembro 4)  –  prefijo /api
|--------------------------------------------------------------------------
| Consejos publicados y foro de salud para la app React Native.
| Requiere token de Sanctum y cuenta activa. Formato de respuesta:
| { "success": bool, "message": string, "data": ... }
*/
Route::middleware(['auth:sanctum', 'activo'])->group(function () {
    // Consejos (solo lectura; solo los publicados)
    Route::get('consejos', [ConsejoController::class, 'index']);
    Route::get('consejos/{consejo}', [ConsejoController::class, 'show'])->whereNumber('consejo');

    // Foro
    Route::get('foro', [ForoController::class, 'index']);
    Route::post('foro', [ForoController::class, 'store'])->middleware('throttle:20,1');
    Route::delete('foro/comentarios/{comentario}', [ForoController::class, 'eliminarComentario'])->whereNumber('comentario');
    Route::get('foro/{post}', [ForoController::class, 'show'])->whereNumber('post');
    Route::delete('foro/{post}', [ForoController::class, 'destroy'])->whereNumber('post');
    Route::post('foro/{post}/comentarios', [ForoController::class, 'comentar'])
        ->whereNumber('post')->middleware('throttle:30,1');
});