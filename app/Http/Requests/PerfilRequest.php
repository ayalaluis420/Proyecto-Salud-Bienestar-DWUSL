<?php

namespace App\Http\Requests;

use App\Models\Perfil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación del perfil de salud (edad, sexo, altura, peso).
 * Se usa directamente en el panel/API ("Mi perfil") y sus reglas se reutilizan
 * en los requests de usuarios mediante PerfilRequest::reglas().
 */
class PerfilRequest extends FormRequest
{
    public const EDAD_MIN = 10;
    public const EDAD_MAX = 100;
    public const ALTURA_MIN = 100;   // cm
    public const ALTURA_MAX = 250;
    public const PESO_MIN = 20;      // kg
    public const PESO_MAX = 300;

    public function authorize(): bool
    {
        return true;   // el acceso lo controlan los middleware de la ruta
    }

    public function rules(): array
    {
        return self::reglas(requerido: false);
    }

    /**
     * @param  bool  $requerido  true = los 4 campos son obligatorios; false = opcionales.
     * @param  string  $prefijo  p. ej. "perfil." cuando van anidados bajo "perfil[...]".
     */
    public static function reglas(bool $requerido = false, string $prefijo = ''): array
    {
        $base = $requerido ? ['required'] : ['nullable'];

        return [
            $prefijo.'edad'   => [...$base, 'integer', 'between:'.self::EDAD_MIN.','.self::EDAD_MAX],
            $prefijo.'sexo'   => [...$base, Rule::in(Perfil::SEXOS)],
            $prefijo.'altura' => [...$base, 'numeric', 'between:'.self::ALTURA_MIN.','.self::ALTURA_MAX],
            $prefijo.'peso'   => [...$base, 'numeric', 'between:'.self::PESO_MIN.','.self::PESO_MAX],
        ];
    }

    public function attributes(): array
    {
        return self::nombresCampos();
    }

    public static function nombresCampos(string $prefijo = ''): array
    {
        return [
            $prefijo.'edad'   => 'edad',
            $prefijo.'sexo'   => 'sexo',
            $prefijo.'altura' => 'altura (cm)',
            $prefijo.'peso'   => 'peso (kg)',
        ];
    }
}
