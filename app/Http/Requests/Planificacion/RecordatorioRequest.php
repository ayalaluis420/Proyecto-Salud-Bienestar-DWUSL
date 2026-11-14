<?php

namespace App\Http\Requests\Planificacion;

use Illuminate\Foundation\Http\FormRequest;

class RecordatorioRequest extends FormRequest
{
    public function authorize(): bool
    {
        $registro = $this->route('recordatorio');
        if ($registro && $registro->user_id !== $this->user()?->id) {
            abort(404);
        }

        return $this->user() !== null;
    }

    public function rules(): array
    {
        $r = $this->route('recordatorio');
        $inicio = $this->input('fecha_inicio', $r?->fecha_inicio?->toDateString());
        $fin = $this->input('fecha_fin', $r?->fecha_fin?->toDateString());
        $required = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'nombre' => [$required, 'string', 'max:150'], 'tipo' => [$required, 'in:medicamento,vitamina'],
            'dosis' => [$required, 'string', 'max:100'], 'hora' => [$required, 'date_format:H:i'],
            'frecuencia' => [$required, 'in:diaria,semanal,mensual'], 'activo' => ['sometimes', 'boolean'],
            'fecha_inicio' => [$required, 'date_format:Y-m-d', ...(is_string($fin) && $fin !== '' ? ['before_or_equal:'.$fin] : [])],
            'fecha_fin' => ['nullable', 'date_format:Y-m-d', ...(is_string($inicio) && $inicio !== '' ? ['after_or_equal:'.$inicio] : [])],
        ];
    }
}
