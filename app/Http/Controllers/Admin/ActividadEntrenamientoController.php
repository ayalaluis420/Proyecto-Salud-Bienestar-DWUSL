<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActividadEntrenamientoController extends Controller
{
    public function index(Request $request)
    {
        // Consulta de actividades físicas
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
            ->paginate(10, ['*'], 'pagina_actividades')
            ->withQueryString();

        // Consulta de entrenamientos y sus detalles
        $entrenamientos = DB::table('entrenamientos')
            ->join('users', 'entrenamientos.user_id', '=', 'users.id')
            ->join('entrenamiento_detalles', 'entrenamientos.id', '=', 'entrenamiento_detalles.entrenamiento_id')
            ->join('tipos_ejercicio', 'entrenamiento_detalles.tipo_ejercicio_id', '=', 'tipos_ejercicio.id')
            ->select(
                'entrenamientos.id as entrenamiento_id',
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
            ->when($request->tipo_ejercicio_id, function ($query, $tipoId) {
                $query->where('entrenamiento_detalles.tipo_ejercicio_id', $tipoId);
            })
            ->when($request->intensidad, function ($query, $intensidad) {
                $query->where('entrenamiento_detalles.intensidad', $intensidad);
            })
            ->when($request->fecha_inicio, function ($query, $fecha) {
                $query->whereDate('entrenamientos.fecha', '>=', $fecha);
            })
            ->when($request->fecha_fin, function ($query, $fecha) {
                $query->whereDate('entrenamientos.fecha', '<=', $fecha);
            })
            ->orderByDesc('entrenamientos.fecha')
            ->paginate(10, ['*'], 'pagina_entrenamientos')
            ->withQueryString();

        // Datos para los filtros
        $usuarios = DB::table('users')
            ->orderBy('name')
            ->get(['id', 'name']);

        $tiposEjercicio = DB::table('tipos_ejercicio')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

            // Estadísticas de los últimos 7 días
            $estadisticasSemanales = DB::table('actividades_fisicas')
            ->select(
                'fecha',
                DB::raw('SUM(pasos) as total_pasos'),
                DB::raw('SUM(calorias) as total_calorias')
            )
            ->when($request->usuario_id, function ($query, $usuarioId) {
                $query->where('user_id', $usuarioId);
            })
            ->whereDate('fecha', '>=', now()->subDays(6)->toDateString())
            ->whereDate('fecha', '<=', now()->toDateString())
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

            // Estadísticas de los últimos 30 días
            $estadisticasMensuales = DB::table('actividades_fisicas')
                ->select(
                    'fecha',
                    DB::raw('SUM(pasos) as total_pasos'),
                    DB::raw('SUM(calorias) as total_calorias')
                )
                ->when($request->usuario_id, function ($query, $usuarioId) {
                    $query->where('user_id', $usuarioId);
                })
                ->whereDate('fecha', '>=', now()->subDays(29)->toDateString())
                ->whereDate('fecha', '<=', now()->toDateString())
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get();    

        return view('admin.actividadesEntrenamientos.index', compact(
            'actividades',
            'entrenamientos',
            'usuarios',
            'tiposEjercicio',
            'estadisticasSemanales',
            'estadisticasMensuales'
        ));
    }
}

