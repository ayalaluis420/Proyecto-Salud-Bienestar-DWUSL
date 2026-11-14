<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * NOTA para el equipo: el Miembro 2 integra los seeders de los demás módulos.
     * Cada miembro agrega aquí su propia línea $this->call(...).
     */
    public function run(): void
    {
        $this->call([
            UsuariosSeeder::class,   // Miembro 1
            TipoEjercicioSeeder::class,  // Miembro 2
        ]);
    }
}
