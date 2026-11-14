<?php

use App\Http\Controllers\Api\AlimentoController;
use App\Http\Controllers\Api\ObjetivoController;
use App\Http\Controllers\Api\PlanComidaController;
use App\Http\Controllers\Api\RecordatorioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'activo'])->name('api.')->group(function () {
    Route::apiResource('objetivos', ObjetivoController::class)->parameters(['objetivos' => 'objetivo']);
    Route::apiResource('recordatorios', RecordatorioController::class)->parameters(['recordatorios' => 'recordatorio']);
    Route::post('recordatorios/{recordatorio}/cumplimientos', [RecordatorioController::class, 'cumplimiento']);
    Route::delete('recordatorios/{recordatorio}/cumplimientos', [RecordatorioController::class, 'cumplimiento']);
    Route::apiResource('alimentos', AlimentoController::class)->only(['index', 'show']);
    Route::apiResource('planes-comida', PlanComidaController::class)->parameters(['planes-comida' => 'plan']);
});
