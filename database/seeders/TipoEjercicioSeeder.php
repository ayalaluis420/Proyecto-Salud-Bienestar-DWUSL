<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoEjercicio;

class TipoEjercicioSeeder extends Seeder
{
    public function run(): void
    {
        $ejercicios = [
            [
                'nombre' => 'Caminar',
                'descripcion' => 'Actividad física de caminata.',
                'calorias_por_minuto' => 3.50,
                'activo' => true
            ],
            [
                'nombre' => 'Correr',
                'descripcion' => 'Actividad física de carrera.',
                'calorias_por_minuto' => 10.00,
                'activo' => true
            ],
            [
                'nombre' => 'Bicicleta',
                'descripcion' => 'Ejercicio realizado en bicicleta.',
                'calorias_por_minuto' => 7.00,
                'activo' => true
            ],
            [
                'nombre' => 'Natación',
                'descripcion' => 'Ejercicio físico realizado en el agua.',
                'calorias_por_minuto' => 8.00,
                'activo' => true
            ],
            [
                'nombre' => 'Saltar cuerda',
                'descripcion' => 'Ejercicio cardiovascular con cuerda.',
                'calorias_por_minuto' => 12.00,
                'activo' => true
            ]
        ];

        foreach ($ejercicios as $ejercicio) {
            TipoEjercicio::updateOrCreate(
                ['nombre' => $ejercicio['nombre']],
                $ejercicio
            );
        }
    }
}
