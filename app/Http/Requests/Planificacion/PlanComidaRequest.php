<?php

namespace App\Http\Requests\Planificacion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlanComidaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $registro = $this->route('plan');
        if ($registro && $registro->user_id !== $this->user()?->id) {
            abort(404);
        }

        return $this->user() !== null;
    }

    public function rules(): array
    {
        $plan = $this->route('plan');
        $tipo = $this->input('tipo');

        return [
            'fecha' => ['required', 'date_format:Y-m-d', Rule::unique('planes_comida')->where(fn ($q) => $q->where('user_id', $this->user()->id)->where('tipo', is_string($tipo) ? $tipo : ''))->ignore($plan)],
            'tipo' => ['required', 'in:desayuno,almuerzo,cena,merienda'], 'notas' => ['nullable', 'string', 'max:2000'],
            'alimentos' => ['required', 'array', 'min:1', 'max:50'],
            'alimentos.*.alimento_id' => ['required', 'integer', 'distinct', 'exists:alimentos,id'],
            'alimentos.*.porciones' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:1000'],
        ];
    }
}
