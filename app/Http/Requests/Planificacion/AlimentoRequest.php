<?php

namespace App\Http\Requests\Planificacion;

use App\Models\Alimento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AlimentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->nombre)) {
            $this->merge(['nombre' => trim($this->nombre)]);
        }
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150', Rule::unique('alimentos')->ignore($this->route('alimento'))],
            'categoria' => ['required', Rule::in(Alimento::CATEGORIAS)],
            'calorias' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:100000'],
            'porcion_gramos' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:100000'],
        ];
    }
}
