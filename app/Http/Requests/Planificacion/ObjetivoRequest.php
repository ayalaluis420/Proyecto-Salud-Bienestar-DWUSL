<?php

namespace App\Http\Requests\Planificacion;

use Illuminate\Foundation\Http\FormRequest;

class ObjetivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $registro = $this->route('objetivo');
        if ($registro && $registro->user_id !== $this->user()?->id) {
            abort(404);
        }

        return $this->user() !== null;
    }

    public function rules(): array
    {
        $objetivo = $this->route('objetivo');
        $inicio = $this->input('fecha_inicio', $objetivo?->fecha_inicio?->toDateString());
        $fin = $this->input('fecha_fin', $objetivo?->fecha_fin?->toDateString());
        $required = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'titulo' => [$required, 'string', 'max:150'], 'descripcion' => ['nullable', 'string', 'max:2000'],
            'unidad' => [$required, 'string', 'max:30'], 'valor_meta' => [$required, 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'valor_actual' => ['sometimes', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'fecha_inicio' => [$required, 'date_format:Y-m-d', ...(is_string($fin) && $fin !== '' ? ['before_or_equal:'.$fin] : [])],
            'fecha_fin' => [$required, 'date_format:Y-m-d', ...(is_string($inicio) && $inicio !== '' ? ['after_or_equal:'.$inicio] : [])],
        ];
    }
}
