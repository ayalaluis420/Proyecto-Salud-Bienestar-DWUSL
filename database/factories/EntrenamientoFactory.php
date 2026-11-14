<?php

namespace Database\Factories;

use App\Models\Entrenamiento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entrenamiento>
 */
class EntrenamientoFactory extends Factory
{
    protected $model = Entrenamiento::class;

    public function definition(): array
    {
        return [
            'user_id' => User::query()->inRandomOrder()->value('id'),
            'fecha' => fake()->dateTimeBetween(
                '-30 days',
                'now'
            ),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}

