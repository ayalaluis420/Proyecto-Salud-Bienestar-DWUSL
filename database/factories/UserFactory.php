<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
        ];
    }

    // "rol" y "activo" no son fillable, por eso se asignan con afterMaking/state
    // (los states de Factory sí pueden escribir cualquier columna).

    public function admin(): static
    {
        return $this->state(fn () => ['rol' => User::ROL_ADMIN, 'activo' => true]);
    }

    public function moderador(): static
    {
        return $this->state(fn () => ['rol' => User::ROL_MODERADOR, 'activo' => true]);
    }

    public function usuario(): static
    {
        return $this->state(fn () => ['rol' => User::ROL_USUARIO, 'activo' => true]);
    }

    public function inactivo(): static
    {
        return $this->state(fn () => ['activo' => false]);
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
