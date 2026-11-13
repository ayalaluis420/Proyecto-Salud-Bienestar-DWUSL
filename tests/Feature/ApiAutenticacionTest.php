<?php

namespace Tests\Feature;

use App\Models\Perfil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class ApiAutenticacionTest extends TestCase
{
    use RefreshDatabase;

    private function datosRegistro(array $extra = []): array
    {
        return array_merge([
            'name'                  => 'Ana López',
            'email'                 => 'ana@correo.com',
            'password'              => 'Segura#2026',
            'password_confirmation' => 'Segura#2026',
            'edad'                  => 28,
            'sexo'                  => 'femenino',
            'altura'                => 165.5,
            'peso'                  => 60.2,
        ], $extra);
    }

    public function test_registro_devuelve_token_y_crea_usuario_con_perfil(): void
    {
        $this->postJson('/api/auth/registro', $this->datosRegistro())
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.usuario.rol', 'usuario')
            ->assertJsonPath('data.usuario.perfil.edad', 28)
            ->assertJsonStructure(['data' => ['token', 'usuario' => ['id', 'nombre', 'email', 'perfil' => ['imc']]]]);

        $this->assertDatabaseHas('users', ['email' => 'ana@correo.com', 'rol' => 'usuario', 'activo' => 1]);
    }

    public function test_registro_no_permite_escalar_privilegios(): void
    {
        $this->postJson('/api/auth/registro', $this->datosRegistro(['rol' => 'admin', 'activo' => false]))
            ->assertCreated();

        $usuario = User::where('email', 'ana@correo.com')->first();
        $this->assertSame('usuario', $usuario->rol);
        $this->assertTrue($usuario->activo);
    }

    public function test_registro_valida_los_datos(): void
    {
        User::factory()->create(['email' => 'ana@correo.com']);

        $this->postJson('/api/auth/registro', $this->datosRegistro([
            'password' => 'debil', 'password_confirmation' => 'otra', 'edad' => 3, 'peso' => 999, 'altura' => 20, 'sexo' => 'x',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password', 'edad', 'peso', 'altura', 'sexo']);
    }

    public function test_login_correcto(): void
    {
        $u = User::factory()->usuario()->create(['email' => 'ana@correo.com']);

        $this->postJson('/api/auth/login', ['email' => 'ANA@correo.com ', 'password' => 'password', 'dispositivo' => 'Pixel 8'])
            ->assertOk()
            ->assertJsonPath('data.usuario.id', $u->id)
            ->assertJsonStructure(['data' => ['token']]);

        $this->assertDatabaseHas('personal_access_tokens', ['tokenable_id' => $u->id, 'name' => 'Pixel 8']);
    }

    public function test_login_con_credenciales_incorrectas_da_401_con_mensaje_unico(): void
    {
        $u = User::factory()->usuario()->create();

        $malaClave = $this->postJson('/api/auth/login', ['email' => $u->email, 'password' => 'mala'])
            ->assertUnauthorized()->json('message');
        $noExiste = $this->postJson('/api/auth/login', ['email' => 'nadie@x.com', 'password' => 'mala'])
            ->assertUnauthorized()->json('message');

        $this->assertSame($malaClave, $noExiste);   // no revela si el correo existe
    }

    public function test_login_de_cuenta_desactivada_da_403(): void
    {
        $u = User::factory()->usuario()->inactivo()->create();

        $this->postJson('/api/auth/login', ['email' => $u->email, 'password' => 'password'])->assertForbidden();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_rutas_protegidas_exigen_token_y_responden_json(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized()->assertJsonStructure(['message']);
        $this->get('/api/auth/me')->assertUnauthorized();   // incluso sin cabecera Accept: nunca redirige al login
    }

    public function test_me_con_token_valido(): void
    {
        $u = User::factory()->usuario()->create();
        $token = $u->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $u->email)
            ->assertJsonMissingPath('data.password');
    }

    public function test_logout_revoca_el_token(): void
    {
        $u = User::factory()->usuario()->create();
        $token = $u->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/auth/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_logout_de_todos_los_dispositivos(): void
    {
        $u = User::factory()->usuario()->create();
        $u->createToken('a');
        $u->createToken('b');
        $token = $u->createToken('c')->plainTextToken;

        $this->withToken($token)->postJson('/api/auth/logout', ['todos' => true])->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_token_de_usuario_desactivado_deja_de_funcionar(): void
    {
        $u = User::factory()->usuario()->create();
        $token = $u->createToken('test')->plainTextToken;

        $u->activo = false;
        $u->save();

        $this->withToken($token)->getJson('/api/auth/me')->assertForbidden();
    }

    public function test_actualizar_y_consultar_el_perfil_propio(): void
    {
        $u = User::factory()->usuario()->create();
        $token = $u->createToken('test')->plainTextToken;

        $this->withToken($token)->putJson('/api/perfil', ['edad' => 30, 'sexo' => 'otro', 'altura' => 180, 'peso' => 81.5])
            ->assertOk()
            ->assertJsonPath('data.perfil.peso', 81.5)
            ->assertJsonPath('data.perfil.imc', 25.2);

        $this->withToken($token)->putJson('/api/perfil', ['edad' => 500])->assertStatus(422)->assertJsonValidationErrors('edad');
    }

    public function test_peso_y_altura_se_guardan_cifrados_en_la_base_de_datos(): void
    {
        $perfil = Perfil::factory()->create(['peso' => 72.5, 'altura' => 170]);

        $crudo = DB::table('perfiles')->where('id', $perfil->id)->first();

        $this->assertNotEquals('72.5', $crudo->peso);          // en BD no se ve el valor real
        $this->assertStringNotContainsString('72.5', $crudo->peso);
        $this->assertSame('72.5', Crypt::decryptString($crudo->peso));
        $this->assertSame(72.5, $perfil->fresh()->peso);       // pero el modelo lo entrega descifrado
    }

    public function test_la_contrasena_se_guarda_con_hash(): void
    {
        $this->postJson('/api/auth/registro', $this->datosRegistro())->assertCreated();

        $guardada = DB::table('users')->where('email', 'ana@correo.com')->value('password');

        $this->assertNotSame('Segura#2026', $guardada);
        $this->assertStringStartsWith('$2y$', $guardada);   // bcrypt
    }
}
