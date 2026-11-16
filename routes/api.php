<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PerfilController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ActividadFisicaController;   

/*
|--------------------------------------------------------------------------
| API para la app React Native  (prefijo /api)
|--------------------------------------------------------------------------
| Autenticación: Laravel Sanctum con tokens personales.
|   Authorization: Bearer <token>        Accept: application/json
| Formato: { "success": bool, "message": string, "data": ... }
|
| NOTA para el equipo: cada miembro agrega sus rutas dentro del grupo
| Route::middleware(['auth:sanctum', 'activo']) de más abajo.
*/

// Públicas (con límite de intentos para frenar fuerza bruta)
Route::prefix('auth')->group(function () {
    Route::post('registro', [AuthController::class, 'registro'])->middleware('throttle:10,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

// Protegidas: token válido y cuenta activa
Route::middleware(['auth:sanctum', 'activo'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::get('perfil', [PerfilController::class, 'show']);
    Route::put('perfil', [PerfilController::class, 'update']);

    // ... rutas de los otros módulos (actividad, objetivos, foro, etc.)

    //Actividades fisicas
    Route::get('actividades', [ActividadFisicaController::class, 'index']);
    Route::post('actividades', [ActividadFisicaController::class, 'store']);
});

require __DIR__.'/planificacion_api.php';
require __DIR__.'/comunidad_api.php';