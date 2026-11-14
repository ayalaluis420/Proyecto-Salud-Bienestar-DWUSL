<?php

namespace Database\Factories;

use App\Models\Entrenamiento;
use App\Models\TipoEjercicio;
use App\Models\EntrenamientoDetalle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EntrenamientoDetalle>
 */
class EntrenamientoDetalleFactory extends Factory
{
    protected $model = EntrenamientoDetalle::class;

    public function definition(): array
    {
        $duracion = fake()->numberBetween(10, 90);

        $tipoEjercicio = TipoEjercicio::query()
            ->inRandomOrder()
            ->first();

        return [
            'entrenamiento_id' => Entrenamiento::query()
                ->inRandomOrder()
                ->value('id'),

            'tipo_ejercicio_id' => $tipoEjercicio->id,

            'duracion_min' => $duracion,

            'intensidad' => fake()->randomElement([
                'Baja',
                'Media',
                'Alta'
            ]),

            'calorias' => round(
                $duracion * $tipoEjercicio->calorias_por_minuto,
                2
            ),
        ];
    }
}

