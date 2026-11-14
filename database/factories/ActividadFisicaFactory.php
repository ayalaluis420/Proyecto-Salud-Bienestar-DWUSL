<?php

namespace Database\Factories;

use App\Models\ActividadFisica;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActividadFisica>
 */
class ActividadFisicaFactory extends Factory
{
    protected $model = ActividadFisica::class;

    public function definition(): array
    {
        return [
            'user_id' => User::query()->inRandomOrder()->value('id'),
            'fecha' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'pasos' => fake()->numberBetween(1000, 15000),
            'distancia_km' => fake()->randomFloat(2, 0.5, 12),
            'calorias' => fake()->randomFloat(2, 50, 800),
        ];
    }
}

