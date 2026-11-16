<?php

namespace App\Http\Requests;

use App\Models\Consejo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Alta y edición de consejos desde el panel (solo admin). */
class ConsejoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'titulo'    => is_string($this->titulo) ? trim($this->titulo) : $this->titulo,
            'contenido' => is_string($this->contenido) ? trim($this->contenido) : $this->contenido,
        ]);
    }

    public function rules(): array
    {
        return [
            'titulo'    => ['required', 'string', 'min:5', 'max:150'],
            'categoria' => ['required', Rule::in(array_keys(Consejo::CATEGORIAS))],
            'contenido' => ['required', 'string', 'min:20', 'max:2000'],
            'publicado' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'titulo'    => 'título',
            'categoria' => 'categoría',
            'contenido' => 'contenido',
        ];
    }
}