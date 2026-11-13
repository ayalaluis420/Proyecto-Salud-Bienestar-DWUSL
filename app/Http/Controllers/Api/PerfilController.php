<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PerfilRequest;
use App\Http\Resources\UsuarioResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Perfil del usuario autenticado en la app móvil (edad, sexo, altura, peso). */
class PerfilController extends Controller
{
    /** GET /api/perfil */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Perfil del usuario.',
            'data'    => new UsuarioResource($request->user()->load('perfil')),
        ]);
    }

    /** PUT /api/perfil */
    public function update(PerfilRequest $request): JsonResponse
    {
        $usuario = $request->user();

        // Solo se actualizan los campos enviados; el usuario siempre edita SU propio perfil.
        $usuario->perfil()->updateOrCreate([], $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado.',
            'data'    => new UsuarioResource($usuario->load('perfil')),
        ]);
    }
}
