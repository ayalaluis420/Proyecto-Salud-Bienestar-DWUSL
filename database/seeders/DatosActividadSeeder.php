<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ActividadFisica;
use App\Models\Entrenamiento;
use App\Models\EntrenamientoDetalle;
use App\Models\TipoEjercicio;
use Carbon\Carbon;

class DatosActividadSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = User::all();
        $tiposEjercicio = TipoEjercicio::where('activo', true)->get();

        if ($usuarios->isEmpty()) {
            $this->command->warn(
                'No existen usuarios. Primero deben registrarse usuarios.'
            );
            return;
        }

        if ($tiposEjercicio->isEmpty()) {
            $this->command->warn(
                'No existen tipos de ejercicio activos.'
            );
            return;
        }

        // Generar actividades físicas de los últimos 7 días.
        foreach ($usuarios as $usuario) {
            for ($dia = 0; $dia < 7; $dia++) {
                $fecha = Carbon::today()
                    ->subDays($dia)
                    ->toDateString();

                $actividad = ActividadFisica::factory()->make([
                    'user_id' => $usuario->id,
                    'fecha' => $fecha
                ]);

                ActividadFisica::updateOrCreate(
                    [
                        'user_id' => $usuario->id,
                        'fecha' => $fecha
                    ],
                    $actividad->getAttributes()
                );
            }
        }

        // Generar dos entrenamientos por usuario.
        foreach ($usuarios as $usuario) {
            Entrenamiento::factory()
                ->count(2)
                ->create([
                    'user_id' => $usuario->id
                ])
                ->each(function ($entrenamiento) use ($tiposEjercicio) {

                    // Generar entre uno y tres ejercicios por entrenamiento.
                    $cantidad = fake()->numberBetween(1, 3);

                    for ($i = 0; $i < $cantidad; $i++) {
                        $tipo = $tiposEjercicio->random();

                        $duracion = fake()->numberBetween(10, 90);

                        EntrenamientoDetalle::factory()->create([
                            'entrenamiento_id' => $entrenamiento->id,
                            'tipo_ejercicio_id' => $tipo->id,
                            'duracion_min' => $duracion,
                            'intensidad' => fake()->randomElement([
                                'Baja',
                                'Media',
                                'Alta'
                            ]),
                            'calorias' => round(
                                $duracion * $tipo->calorias_por_minuto,
                                2
                            )
                        ]);
                    }
                });
        }

        $this->command->info(
            'Los datos de actividades y entrenamientos se generaron correctamente.'
        );
    }
}

