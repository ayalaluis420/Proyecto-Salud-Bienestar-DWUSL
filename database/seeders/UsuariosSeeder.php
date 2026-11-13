<?php

namespace Database\Seeders;

use App\Models\Perfil;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Miembro 1 – Usuarios de prueba (1 admin, 1 moderador, usuarios móviles).
 *
 * Contraseñas de DESARROLLO (cámbialas en producción):
 *   admin@salud.test      / Admin#2026
 *   moderador@salud.test  / Moderador#2026
 *   usuario@salud.test    / Usuario#2026
 */
class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        $this->crear('Administrador', 'admin@salud.test', 'Admin#2026', User::ROL_ADMIN, 35, 'masculino', 175, 78);
        $this->crear('Moderadora', 'moderador@salud.test', 'Moderador#2026', User::ROL_MODERADOR, 29, 'femenino', 165, 60);
        $this->crear('Usuario Demo', 'usuario@salud.test', 'Usuario#2026', User::ROL_USUARIO, 22, 'otro', 170, 65);

        // Usuarios adicionales para probar listados, filtros y reportes
        User::factory()->count(12)->usuario()->create()->each(
            fn (User $u) => Perfil::factory()->create(['user_id' => $u->id])
        );
        User::factory()->count(3)->usuario()->inactivo()->create()->each(
            fn (User $u) => Perfil::factory()->create(['user_id' => $u->id])
        );
    }

    private function crear(string $nombre, string $correo, string $clave, string $rol, int $edad, string $sexo, float $altura, float $peso): void
    {
        $user = User::where('email', $correo)->first() ?? new User(['email' => $correo]);
        $user->name = $nombre;
        $user->password = $clave;          // el cast "hashed" la convierte en hash
        $user->rol = $rol;                 // rol/activo no son fillable: se asignan a propósito
        $user->activo = true;
        $user->email_verified_at = now();
        $user->save();

        $user->perfil()->updateOrCreate([], compact('edad', 'sexo', 'altura', 'peso'));
    }
}
