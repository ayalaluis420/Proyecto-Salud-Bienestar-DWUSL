<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Formato JSON único del usuario para la API (acordado con el equipo React Native).
 *
 * @mixin \App\Models\User
 */
class UsuarioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $perfil = $this->perfil;

        return [
            'id'         => $this->id,
            'nombre'     => $this->name,
            'email'      => $this->email,
            'rol'        => $this->rol,
            'activo'     => (bool) $this->activo,
            'perfil'     => $perfil ? [
                'edad'   => $perfil->edad,
                'sexo'   => $perfil->sexo,
                'altura' => $perfil->altura,   // cm
                'peso'   => $perfil->peso,     // kg
                'imc'    => $perfil->imc,
            ] : null,
            'creado_en'  => $this->created_at?->toIso8601String(),
        ];
    }
}
