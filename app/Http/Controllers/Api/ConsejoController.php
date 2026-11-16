<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consejo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Consejos de salud para la app: solo lectura y solo los publicados. */
class ConsejoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'categoria' => ['nullable', 'in:'.implode(',', array_keys(Consejo::CATEGORIAS))],
            'buscar'    => ['nullable', 'string', 'max:100'],
        ]);

        $consejos = Consejo::publicados()
            ->filtrar($filtros)
            ->latest('id')
            ->paginate(20)
            ->through(fn (Consejo $c) => $this->formato($c));

        return response()->json(['success' => true, 'message' => 'Consejos de salud.', 'data' => $consejos]);
    }

    public function show(Consejo $consejo): JsonResponse
    {
        abort_unless($consejo->publicado, 404); // los ocultos no existen para la app

        $consejo->increment('vistas'); // alimenta el reporte de "más consultados"

        return response()->json(['success' => true, 'message' => 'Consejo de salud.', 'data' => $this->formato($consejo)]);
    }

    private function formato(Consejo $c): array
    {
        return [
            'id'               => $c->id,
            'titulo'           => $c->titulo,
            'categoria'        => $c->categoria,
            'categoria_nombre' => $c->nombre_categoria,
            'contenido'        => $c->contenido,
            'vistas'           => $c->vistas,
            'creado'           => $c->created_at?->toIso8601String(),
        ];
    }
}