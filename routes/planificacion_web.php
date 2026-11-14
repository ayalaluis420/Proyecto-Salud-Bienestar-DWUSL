<?php

use App\Http\Controllers\Admin\AlimentoController;
use App\Http\Controllers\Admin\PlanificacionController;
use App\Http\Controllers\Admin\ReportePlanificacionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'activo', 'es_moderador'])->prefix('admin')->group(function () {
    Route::get('objetivos', [PlanificacionController::class, 'objetivos'])->name('objetivos.index');
    Route::get('recordatorios', [PlanificacionController::class, 'recordatorios'])->name('recordatorios.index');
    Route::get('planes-comida', [PlanificacionController::class, 'comidas'])->name('planes-comida.index');
    Route::middleware('es_admin')->group(function () {
        Route::resource('alimentos', AlimentoController::class)->except(['index', 'show']);
        Route::get('planificacion/reportes', [ReportePlanificacionController::class, 'index'])->name('planificacion.reportes');
        Route::get('planificacion/reportes/{tipo}/{formato}', [ReportePlanificacionController::class, 'descargar'])->whereIn('tipo', ['objetivos', 'recordatorios'])->whereIn('formato', ['pdf', 'excel'])->name('planificacion.descargar');
    });
    Route::get('alimentos', [AlimentoController::class, 'index'])->name('alimentos.index');
});
