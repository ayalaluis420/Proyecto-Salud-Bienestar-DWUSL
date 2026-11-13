<?php

namespace App\Http\Requests\Usuarios;

use App\Http\Requests\PerfilRequest;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Alta de usuarios desde el panel (solo admin). */
class GuardarUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdmin() ?? false;
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
            'name'     => ['required', 'string', 'min:3', 'max:255'],
            'email'    => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'rol'      => ['required', Rule::in(array_keys(User::ROLES))],
            'activo'   => ['nullable', 'boolean'],
            ...PerfilRequest::reglas(requerido: false, prefijo: 'perfil.'),
        ];
    }

    public function attributes(): array
    {
        return [
            'name'                  => 'nombre',
            'email'                 => 'correo electrónico',
            'password'              => 'contraseña',
            'password_confirmation' => 'confirmación de contraseña',
            'rol'                   => 'rol',
            ...PerfilRequest::nombresCampos('perfil.'),
        ];
    }
}
