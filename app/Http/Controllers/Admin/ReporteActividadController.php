<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteActividadController extends Controller
{
    public function pdf(Request $request)
    {
        $request->validate([
            'usuario_id' => 'nullable|integer|exists:users,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        // Consultar actividades físicas
        $actividades = DB::table('actividades_fisicas')
            ->join('users', 'actividades_fisicas.user_id', '=', 'users.id')
            ->select(
                'actividades_fisicas.*',
                'users.name as usuario'
            )
            ->when($request->usuario_id, function ($query, $usuarioId) {
                $query->where('actividades_fisicas.user_id', $usuarioId);
            })
            ->when($request->fecha_inicio, function ($query, $fecha) {
                $query->whereDate('actividades_fisicas.fecha', '>=', $fecha);
            })
            ->when($request->fecha_fin, function ($query, $fecha) {
                $query->whereDate('actividades_fisicas.fecha', '<=', $fecha);
            })
            ->orderByDesc('actividades_fisicas.fecha')
            ->get();

        // Consultar entrenamientos
        $entrenamientos = DB::table('entrenamientos')
            ->join('users', 'entrenamientos.user_id', '=', 'users.id')
            ->join('entrenamiento_detalles', 'entrenamientos.id', '=', 'entrenamiento_detalles.entrenamiento_id')
            ->join('tipos_ejercicio', 'entrenamiento_detalles.tipo_ejercicio_id', '=', 'tipos_ejercicio.id')
            ->select(
                'entrenamientos.fecha',
                'users.name as usuario',
                'tipos_ejercicio.nombre as ejercicio',
                'entrenamiento_detalles.duracion_min',
                'entrenamiento_detalles.intensidad',
                'entrenamiento_detalles.calorias'
            )
            ->when($request->usuario_id, function ($query, $usuarioId) {
                $query->where('entrenamientos.user_id', $usuarioId);
            })
            ->when($request->fecha_inicio, function ($query, $fecha) {
                $query->whereDate('entrenamientos.fecha', '>=', $fecha);
            })
            ->when($request->fecha_fin, function ($query, $fecha) {
                $query->whereDate('entrenamientos.fecha', '<=', $fecha);
            })
            ->orderByDesc('entrenamientos.fecha')
            ->get();

        $pdf = Pdf::loadView('admin.reportes.actividades', compact(
            'actividades',
            'entrenamientos',
            'request'
        ));

        return $pdf->download('reporte_actividades.pdf');
    }
}
