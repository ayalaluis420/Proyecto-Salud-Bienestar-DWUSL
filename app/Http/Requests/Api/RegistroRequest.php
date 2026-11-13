<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\PerfilRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Registro público desde la app móvil. Nunca acepta "rol" ni "activo". */
class RegistroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'  => is_string($this->name) ? trim($this->name) : $this->name,
            'email' => is_string($this->email) ? mb_strtolower(trim($this->email)) : $this->email,
        ]);
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'min:3', 'max:255'],
            'email'                 => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'password'              => ['required', 'string', 'confirmed', Password::defaults()],
            'dispositivo'           => ['nullable', 'string', 'max:100'],   // nombre del token (p. ej. "iPhone de Ana")
            ...PerfilRequest::reglas(requerido: false),                     // edad, sexo, altura, peso (opcionales)
        ];
    }

    public function attributes(): array
    {
        return [
            'name'                  => 'nombre',
            'email'                 => 'correo electrónico',
            'password'              => 'contraseña',
            'password_confirmation' => 'confirmación de contraseña',
            ...PerfilRequest::nombresCampos(),
        ];
    }
}
