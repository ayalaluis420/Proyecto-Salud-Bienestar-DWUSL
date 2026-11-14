<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActividadFisica;
use Illuminate\Http\Request;

class ActividadFisicaController extends Controller
{
    // Consultar las actividades del usuario autenticado
    public function index(Request $request)
    {
        $actividades = ActividadFisica::where('user_id', $request->user()->id)
            ->orderByDesc('fecha')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Actividades físicas consultadas correctamente.',
            'data' => $actividades
        ], 200);
    }

    // Registrar o actualizar la actividad física diaria
    public function store(Request $request)
    {
        $datos = $request->validate([
            'fecha' => 'required|date',
            'pasos' => 'required|integer|min:0',
            'distancia_km' => 'required|numeric|min:0',
            'calorias' => 'required|numeric|min:0'
        ]);

        $actividad = ActividadFisica::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'fecha' => $datos['fecha']
            ],
            [
                'pasos' => $datos['pasos'],
                'distancia_km' => $datos['distancia_km'],
                'calorias' => $datos['calorias']
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Actividad física registrada correctamente.',
            'data' => $actividad
        ], 200);
    }
}
