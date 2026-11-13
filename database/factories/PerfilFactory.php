<?php

namespace Database\Factories;

use App\Models\Perfil;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Perfil>
 */
class PerfilFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'edad'    => fake()->numberBetween(15, 70),
            'sexo'    => fake()->randomElement(Perfil::SEXOS),
            'altura'  => fake()->randomFloat(1, 150, 195),   // cm
            'peso'    => fake()->randomFloat(1, 50, 110),    // kg
        ];
    }
}
