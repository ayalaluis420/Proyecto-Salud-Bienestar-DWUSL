<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AutenticacionPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_iniciar_sesion_y_entra_al_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect('/home');

        $this->assertAuthenticatedAs($admin);
        $this->get('/home')->assertOk()->assertSee('Administrador');
    }

    public function test_moderador_puede_iniciar_sesion(): void
    {
        $mod = User::factory()->moderador()->create();

        $this->post('/login', ['email' => $mod->email, 'password' => 'password'])->assertRedirect('/home');
        $this->assertAuthenticatedAs($mod);
    }

    public function test_usuario_de_la_app_movil_no_entra_al_panel(): void
    {
        $usuario = User::factory()->usuario()->create();

        $this->from('/login')->post('/login', ['email' => $usuario->email, 'password' => 'password'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_usuario_inactivo_no_puede_iniciar_sesion(): void
    {
        $admin = User::factory()->admin()->inactivo()->create();

        $this->from('/login')->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_credenciales_incorrectas_son_rechazadas(): void
    {
        $admin = User::factory()->admin()->create();

        $this->from('/login')->post('/login', ['email' => $admin->email, 'password' => 'incorrecta'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_se_bloquea_tras_cinco_intentos_fallidos(): void
    {
        $admin = User::factory()->admin()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $admin->email, 'password' => 'mala']);
        }

        // Aun con la contraseña correcta, el sexto intento queda bloqueado temporalmente
        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_cerrar_sesion(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/logout')->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get('/home')->assertRedirect('/login');
        $this->get('/admin/usuarios')->assertRedirect('/login');
    }

    public function test_el_registro_publico_esta_desactivado(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', ['name' => 'X', 'email' => 'x@x.com', 'password' => 'Abcdef1#', 'password_confirmation' => 'Abcdef1#'])
            ->assertStatus(404);
    }

    public function test_recuperacion_de_contrasena(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();

        $this->post('/password/email', ['email' => $admin->email])->assertSessionHas('status');
        Notification::assertSentTo($admin, ResetPassword::class);

        $token = Password::createToken($admin);
        $this->post('/password/reset', [
            'token'                 => $token,
            'email'                 => $admin->email,
            'password'              => 'Nueva#Clave2026',
            'password_confirmation' => 'Nueva#Clave2026',
        ])->assertRedirect('/login');

        $this->assertTrue(Hash::check('Nueva#Clave2026', $admin->fresh()->password));
        $this->assertGuest();   // no inicia sesión automáticamente
    }

    public function test_la_recuperacion_exige_contrasena_segura(): void
    {
        $admin = User::factory()->admin()->create();
        $token = Password::createToken($admin);

        $this->post('/password/reset', [
            'token' => $token, 'email' => $admin->email,
            'password' => '12345678', 'password_confirmation' => '12345678',
        ])->assertSessionHasErrors('password');
    }

    public function test_cuenta_desactivada_con_sesion_abierta_pierde_el_acceso(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $admin->activo = false;
        $admin->save();

        $this->get('/home')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_el_panel_envia_cabeceras_de_seguridad(): void
    {
        $this->get('/login')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
