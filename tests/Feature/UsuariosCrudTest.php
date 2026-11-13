<?php

namespace Tests\Feature;

use App\Models\Perfil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsuariosCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function datosValidos(array $extra = []): array
    {
        return array_merge([
            'name'                  => 'Carlos Pérez',
            'email'                 => 'carlos@correo.com',
            'password'              => 'Clave#Segura1',
            'password_confirmation' => 'Clave#Segura1',
            'rol'                   => 'usuario',
            'activo'                => '1',
            'perfil'                => ['edad' => 40, 'sexo' => 'masculino', 'altura' => 172.5, 'peso' => 80],
        ], $extra);
    }

    // ----------------------------------------------------------- Roles y permisos

    public function test_admin_ve_el_listado_con_todas_las_acciones(): void
    {
        User::factory()->usuario()->create(['name' => 'Persona Visible']);

        $this->actingAs($this->admin())->get('/admin/usuarios')
            ->assertOk()
            ->assertSee('Persona Visible')
            ->assertSee('Nuevo usuario')
            ->assertSee('Exportar PDF');
    }

    public function test_moderador_solo_puede_consultar(): void
    {
        $mod = User::factory()->moderador()->create();
        $otro = User::factory()->usuario()->create();

        $this->actingAs($mod);

        $this->get('/admin/usuarios')->assertOk()->assertDontSee('Nuevo usuario')->assertDontSee('Exportar PDF');
        $this->get("/admin/usuarios/{$otro->id}")->assertOk();

        $this->get('/admin/usuarios/create')->assertForbidden();
        $this->post('/admin/usuarios', $this->datosValidos())->assertForbidden();
        $this->get("/admin/usuarios/{$otro->id}/edit")->assertForbidden();
        $this->put("/admin/usuarios/{$otro->id}", $this->datosValidos())->assertForbidden();
        $this->patch("/admin/usuarios/{$otro->id}/toggle")->assertForbidden();
        $this->delete("/admin/usuarios/{$otro->id}")->assertForbidden();
        $this->get('/admin/usuarios/reporte/pdf')->assertForbidden();
        $this->get('/admin/usuarios/reporte/excel')->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $otro->id]);
    }

    public function test_usuario_comun_no_entra_al_panel(): void
    {
        $this->actingAs(User::factory()->usuario()->create())
            ->get('/admin/usuarios')->assertForbidden();
    }

    // ------------------------------------------------------------------- Crear

    public function test_admin_crea_usuario_con_perfil_y_contrasena_hasheada(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/usuarios', $this->datosValidos(['rol' => 'moderador']))
            ->assertRedirect('/admin/usuarios')
            ->assertSessionHas('success');

        $u = User::where('email', 'carlos@correo.com')->firstOrFail();
        $this->assertSame('moderador', $u->rol);
        $this->assertTrue($u->activo);
        $this->assertTrue(Hash::check('Clave#Segura1', $u->password));
        $this->assertNotSame('Clave#Segura1', $u->getRawOriginal('password'));
        $this->assertSame(40, $u->perfil->edad);
        $this->assertSame(80.0, $u->perfil->peso);
    }

    public function test_crear_sin_activo_deja_la_cuenta_inactiva(): void
    {
        $this->actingAs($this->admin())->post('/admin/usuarios', $this->datosValidos(['activo' => '0']));

        $this->assertFalse(User::where('email', 'carlos@correo.com')->first()->activo);
    }

    public function test_el_perfil_es_opcional(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/usuarios', $this->datosValidos(['perfil' => ['edad' => '', 'sexo' => '', 'altura' => '', 'peso' => '']]))
            ->assertSessionHasNoErrors();

        $this->assertNull(User::where('email', 'carlos@correo.com')->first()->perfil);
    }

    // ------------------------------------------------------------- Validaciones

    public function test_correo_duplicado_es_rechazado(): void
    {
        User::factory()->create(['email' => 'carlos@correo.com']);

        $this->actingAs($this->admin())->from('/admin/usuarios/create')
            ->post('/admin/usuarios', $this->datosValidos())
            ->assertRedirect('/admin/usuarios/create')
            ->assertSessionHasErrors('email');
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('contrasenasInseguras')]
    public function test_contrasena_debil_es_rechazada(string $clave): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/usuarios', $this->datosValidos(['password' => $clave, 'password_confirmation' => $clave]))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'carlos@correo.com']);
    }

    public static function contrasenasInseguras(): array
    {
        return [
            'muy corta'      => ['Ab1#'],
            'sin mayúscula'  => ['clave#segura1'],
            'sin minúscula'  => ['CLAVE#SEGURA1'],
            'sin número'     => ['Clave#Segura'],
            'sin símbolo'    => ['ClaveSegura1'],
        ];
    }

    public function test_la_confirmacion_de_contrasena_debe_coincidir(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/usuarios', $this->datosValidos(['password_confirmation' => 'Otra#Clave123']))
            ->assertSessionHasErrors('password');
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('perfilesInvalidos')]
    public function test_rangos_del_perfil(array $perfil, string $campo): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/usuarios', $this->datosValidos(['perfil' => $perfil]))
            ->assertSessionHasErrors($campo);
    }

    public static function perfilesInvalidos(): array
    {
        return [
            'edad baja'      => [['edad' => 5], 'perfil.edad'],
            'edad alta'      => [['edad' => 150], 'perfil.edad'],
            'edad decimal'   => [['edad' => 20.5], 'perfil.edad'],
            'altura baja'    => [['altura' => 50], 'perfil.altura'],
            'altura alta'    => [['altura' => 300], 'perfil.altura'],
            'peso bajo'      => [['peso' => 5], 'perfil.peso'],
            'peso alto'      => [['peso' => 500], 'perfil.peso'],
            'peso texto'     => [['peso' => 'mucho'], 'perfil.peso'],
            'sexo inválido'  => [['sexo' => 'robot'], 'perfil.sexo'],
        ];
    }

    public function test_rol_invalido_es_rechazado(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/usuarios', $this->datosValidos(['rol' => 'superadmin']))
            ->assertSessionHasErrors('rol');
    }

    public function test_los_errores_se_muestran_en_bootstrap(): void
    {
        $this->actingAs($this->admin())->from('/admin/usuarios/create')
            ->followingRedirects()
            ->post('/admin/usuarios', $this->datosValidos(['email' => 'no-es-correo', 'perfil' => ['edad' => 3]]))
            ->assertSee('is-invalid', false)
            ->assertSee('invalid-feedback', false)
            ->assertSee('El campo correo electrónico debe ser un correo electrónico válido.');
    }

    // ----------------------------------------------------------------- Editar

    public function test_editar_conserva_el_correo_propio_y_la_contrasena_es_opcional(): void
    {
        $u = User::factory()->usuario()->create(['email' => 'viejo@correo.com']);
        $claveAnterior = $u->password;

        $this->actingAs($this->admin())->put("/admin/usuarios/{$u->id}", [
            'name' => 'Nombre Nuevo', 'email' => 'viejo@correo.com', 'rol' => 'moderador', 'activo' => '1',
            'password' => '', 'password_confirmation' => '',
            'perfil' => ['edad' => 33, 'sexo' => 'femenino', 'altura' => 160, 'peso' => 55],
        ])->assertRedirect('/admin/usuarios')->assertSessionHasNoErrors();

        $u->refresh();
        $this->assertSame('Nombre Nuevo', $u->name);
        $this->assertSame('moderador', $u->rol);
        $this->assertSame($claveAnterior, $u->password);
        $this->assertSame(33, $u->perfil->edad);
    }

    public function test_editar_no_permite_tomar_el_correo_de_otro_usuario(): void
    {
        $otro = User::factory()->create(['email' => 'ocupado@correo.com']);
        $u = User::factory()->usuario()->create();

        $this->actingAs($this->admin())->put("/admin/usuarios/{$u->id}", [
            'name' => 'Algo Valido', 'email' => $otro->email, 'rol' => 'usuario', 'activo' => '1',
        ])->assertSessionHasErrors('email');
    }

    public function test_cambiar_contrasena_cierra_las_sesiones_de_la_app(): void
    {
        $u = User::factory()->usuario()->create();
        $u->createToken('movil');

        $this->actingAs($this->admin())->put("/admin/usuarios/{$u->id}", [
            'name' => $u->name, 'email' => $u->email, 'rol' => 'usuario', 'activo' => '1',
            'password' => 'Nueva#Clave99', 'password_confirmation' => 'Nueva#Clave99',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('Nueva#Clave99', $u->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    // ------------------------------------------------------ Activar / desactivar

    public function test_activar_y_desactivar_usuario_revoca_tokens(): void
    {
        $u = User::factory()->usuario()->create();
        $u->createToken('movil');

        $admin = $this->admin();
        $this->actingAs($admin)->patch("/admin/usuarios/{$u->id}/toggle")->assertSessionHas('success');
        $this->assertFalse($u->fresh()->activo);
        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->patch("/admin/usuarios/{$u->id}/toggle");
        $this->assertTrue($u->fresh()->activo);
    }

    public function test_admin_no_puede_desactivarse_ni_quitarse_el_rol(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->patch("/admin/usuarios/{$admin->id}/toggle")->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->activo);

        $this->put("/admin/usuarios/{$admin->id}", [
            'name' => $admin->name, 'email' => $admin->email, 'rol' => 'usuario', 'activo' => '1',
        ])->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->esAdmin());
    }

    public function test_no_se_puede_dejar_al_sistema_sin_administradores_activos(): void
    {
        $unico = $this->admin();
        $actor = User::factory()->admin()->create();   // otro admin que ejecuta la acción

        // Con dos admins activos se puede desactivar a uno...
        $this->actingAs($actor)->patch("/admin/usuarios/{$unico->id}/toggle")->assertSessionHas('success');

        // ...pero ya no se puede eliminar/desactivar al último admin activo desde otra cuenta (actor es el único)
        $this->assertSame(1, User::where('rol', 'admin')->where('activo', true)->count());
        $moderador = User::factory()->moderador()->create();
        $this->actingAs($actor)->delete("/admin/usuarios/{$actor->id}")->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $actor->id]);
        $this->assertDatabaseHas('users', ['id' => $moderador->id]);
    }

    // ----------------------------------------------------------------- Eliminar

    public function test_eliminar_usuario_borra_su_perfil_y_tokens(): void
    {
        $u = User::factory()->usuario()->create();
        $perfil = Perfil::factory()->create(['user_id' => $u->id]);
        $u->createToken('movil');

        $this->actingAs($this->admin())->delete("/admin/usuarios/{$u->id}")
            ->assertRedirect('/admin/usuarios')->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $u->id]);
        $this->assertDatabaseMissing('perfiles', ['id' => $perfil->id]);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_admin_no_puede_eliminarse_a_si_mismo(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->delete("/admin/usuarios/{$admin->id}")->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    // ------------------------------------------------- Filtros y seguridad (SQL)

    public function test_filtros_por_rol_estado_y_busqueda(): void
    {
        User::factory()->moderador()->create(['name' => 'Mod Uno']);
        User::factory()->usuario()->create(['name' => 'Usuario Activo']);
        User::factory()->usuario()->inactivo()->create(['name' => 'Usuario Dormido']);

        $this->actingAs($this->admin());

        $this->get('/admin/usuarios?rol=moderador')->assertSee('Mod Uno')->assertDontSee('Usuario Activo');
        $this->get('/admin/usuarios?estado=inactivo')->assertSee('Usuario Dormido')->assertDontSee('Usuario Activo');
        $this->get('/admin/usuarios?rol=usuario&estado=activo')->assertSee('Usuario Activo')->assertDontSee('Usuario Dormido')->assertDontSee('Mod Uno');
        $this->get('/admin/usuarios?buscar=Dormido')->assertSee('Usuario Dormido')->assertDontSee('Mod Uno');
    }

    public function test_la_busqueda_es_inmune_a_inyeccion_sql(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin/usuarios?buscar='.urlencode("' OR '1'='1"))
            ->assertOk()
            ->assertSee('No se encontraron usuarios');

        $this->get('/admin/usuarios?rol='.urlencode("admin' OR 1=1 --"))->assertOk();
        $this->assertDatabaseCount('users', 1);   // la tabla sigue intacta
    }

    public function test_los_comodines_de_like_se_buscan_literalmente(): void
    {
        User::factory()->usuario()->create(['name' => 'Ana Torres']);

        $this->actingAs($this->admin())->get('/admin/usuarios?buscar=%25')->assertSee('No se encontraron usuarios');
    }

    public function test_no_se_puede_asignar_rol_por_asignacion_masiva(): void
    {
        $u = User::create(['name' => 'Intruso', 'email' => 'i@i.com', 'password' => 'Clave#Segura1', 'rol' => 'admin', 'activo' => false]);

        $this->assertSame('usuario', $u->fresh()->rol);
        $this->assertTrue($u->fresh()->activo);
    }

    public function test_los_formularios_incluyen_token_csrf_y_el_middleware_esta_activo(): void
    {
        // Laravel desactiva la verificación CSRF al ejecutar pruebas, así que se comprueba que:
        // 1) el grupo "web" (panel) incluye el middleware de verificación, y
        // 2) cada formulario de escritura lleva el campo oculto _token.
        $grupoWeb = app('router')->getMiddlewareGroups()['web'];
        $this->assertTrue(
            collect($grupoWeb)->contains(fn ($m) => str_contains((string) $m, 'ValidateCsrfToken') || str_contains((string) $m, 'PreventRequestForgery')),
            'El grupo web debe incluir la verificación CSRF.'
        );

        $this->get('/login')->assertSee('name="_token"', false);

        $this->actingAs($this->admin());
        $this->get('/admin/usuarios/create')->assertSee('name="_token"', false);
    }

    // ------------------------------------------------------------------ Reportes

    public function test_reporte_pdf(): void
    {
        User::factory()->usuario()->count(3)->create();

        $respuesta = $this->actingAs($this->admin())->get('/admin/usuarios/reporte/pdf?rol=usuario&estado=activo');

        $respuesta->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $respuesta->getContent());
    }

    public function test_reporte_excel(): void
    {
        User::factory()->usuario()->count(3)->create();

        $respuesta = $this->actingAs($this->admin())->get('/admin/usuarios/reporte/excel?estado=activo');

        $respuesta->assertOk();
        $this->assertStringContainsString('.xlsx', $respuesta->headers->get('content-disposition'));
    }

    public function test_el_reporte_rechaza_filtros_invalidos(): void
    {
        $this->actingAs($this->admin())->from('/admin/usuarios')
            ->get('/admin/usuarios/reporte/pdf?rol=hacker')
            ->assertSessionHasErrors('rol');
    }
}
