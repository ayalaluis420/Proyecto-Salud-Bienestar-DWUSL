<?php

namespace App\Http\Requests\Planificacion;

use App\Models\Alimento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FiltroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'estado' => ['nullable', 'in:en_curso,cumplido,vencido'],
            'tipo' => ['nullable', 'in:medicamento,vitamina,desayuno,almuerzo,cena,merienda'],
            'activo' => ['nullable', 'boolean'], 'categoria' => ['nullable', Rule::in(Alimento::CATEGORIAS)],
            'buscar' => ['nullable', 'string', 'max:150'], 'fecha' => ['nullable', 'date_format:Y-m-d'],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', ...($this->filled('desde') ? ['after_or_equal:desde'] : [])],
        ];
    }
}
