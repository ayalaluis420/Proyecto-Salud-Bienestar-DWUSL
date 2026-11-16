<?php

namespace Tests\Feature;

use App\Models\Consejo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ConsejosCrudTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioConRol(string $rol): User
    {
        $u = User::factory()->create();
        $u->rol = $rol;
        $u->activo = true;
        $u->save();

        return $u;
    }

    private function datos(array $extra = []): array
    {
        return array_merge([
            'titulo'    => 'Toma agua durante el día',
            'categoria' => 'dieta',
            'contenido' => 'Procura beber entre seis y ocho vasos de agua al día para mantenerte hidratado.',
            'publicado' => '1',
        ], $extra);
    }

    private function crearConsejo(array $extra = []): Consejo
    {
        $c = new Consejo([
            'titulo'    => $extra['titulo'] ?? 'Consejo de prueba',
            'categoria' => $extra['categoria'] ?? 'sueno',
            'contenido' => 'Contenido de prueba para el consejo de salud.',
        ]);
        $c->publicado = $extra['publicado'] ?? true;
        $c->save();

        return $c;
    }

    public function test_invitado_es_enviado_al_login(): void
    {
        $this->get(route('consejos.index'))->assertRedirect(route('login'));
    }

    public function test_moderador_no_puede_gestionar_consejos(): void
    {
        $mod = $this->usuarioConRol(User::ROL_MODERADOR);
        $consejo = $this->crearConsejo();

        $this->actingAs($mod)->get(route('consejos.index'))->assertForbidden();
        $this->actingAs($mod)->post(route('consejos.store'), $this->datos())->assertForbidden();
        $this->actingAs($mod)->delete(route('consejos.destroy', $consejo))->assertForbidden();
        $this->assertDatabaseHas('consejos', ['id' => $consejo->id]);
    }

    public function test_usuario_de_la_app_no_entra(): void
    {
        $this->actingAs($this->usuarioConRol(User::ROL_USUARIO))
            ->get(route('consejos.index'))->assertForbidden();
    }

    public function test_admin_ve_el_listado(): void
    {
        $this->crearConsejo(['titulo' => 'Dormir bien importa']);

        $this->actingAs($this->usuarioConRol(User::ROL_ADMIN))
            ->get(route('consejos.index'))
            ->assertOk()
            ->assertSee('Dormir bien importa');
    }

    public function test_admin_crea_consejo_y_queda_su_autor(): void
    {
        $admin = $this->usuarioConRol(User::ROL_ADMIN);

        $this->actingAs($admin)->post(route('consejos.store'), $this->datos())
            ->assertRedirect(route('consejos.index'));

        $this->assertDatabaseHas('consejos', [
            'titulo'    => 'Toma agua durante el día',
            'categoria' => 'dieta',
            'publicado' => 1,
            'vistas'    => 0,
            'user_id'   => $admin->id,
        ]);
    }

    public function test_sin_marcar_publicado_queda_oculto(): void
    {
        $this->actingAs($this->usuarioConRol(User::ROL_ADMIN))
            ->post(route('consejos.store'), $this->datos(['publicado' => '0']));

        $this->assertDatabaseHas('consejos', ['titulo' => 'Toma agua durante el día', 'publicado' => 0]);
    }

    #[DataProvider('invalidos')]
    public function test_validaciones(array $cambio, string $campo): void
    {
        $this->actingAs($this->usuarioConRol(User::ROL_ADMIN))
            ->post(route('consejos.store'), $this->datos($cambio))
            ->assertSessionHasErrors($campo);

        $this->assertDatabaseCount('consejos', 0);
    }

    public static function invalidos(): array
    {
        return [
            'titulo vacio'       => [['titulo' => ''], 'titulo'],
            'titulo corto'       => [['titulo' => 'abc'], 'titulo'],
            'titulo largo'       => [['titulo' => str_repeat('a', 151)], 'titulo'],
            'categoria invalida' => [['categoria' => 'otra'], 'categoria'],
            'contenido corto'    => [['contenido' => 'muy corto'], 'contenido'],
            'contenido largo'    => [['contenido' => str_repeat('a', 2001)], 'contenido'],
        ];
    }

    public function test_editar_consejo(): void
    {
        $consejo = $this->crearConsejo();

        $this->actingAs($this->usuarioConRol(User::ROL_ADMIN))
            ->put(route('consejos.update', $consejo), $this->datos(['titulo' => 'Titulo actualizado', 'categoria' => 'bienestar']))
            ->assertRedirect(route('consejos.index'));

        $this->assertDatabaseHas('consejos', ['id' => $consejo->id, 'titulo' => 'Titulo actualizado', 'categoria' => 'bienestar']);
    }

    public function test_publicar_y_ocultar(): void
    {
        $consejo = $this->crearConsejo();
        $admin = $this->usuarioConRol(User::ROL_ADMIN);

        $this->actingAs($admin)->patch(route('consejos.toggle', $consejo))->assertSessionHas('success');
        $this->assertFalse($consejo->fresh()->publicado);

        $this->actingAs($admin)->patch(route('consejos.toggle', $consejo));
        $this->assertTrue($consejo->fresh()->publicado);
    }

    public function test_eliminar_consejo(): void
    {
        $consejo = $this->crearConsejo();

        $this->actingAs($this->usuarioConRol(User::ROL_ADMIN))
            ->delete(route('consejos.destroy', $consejo))
            ->assertRedirect(route('consejos.index'));

        $this->assertDatabaseMissing('consejos', ['id' => $consejo->id]);
    }

    public function test_filtros(): void
    {
        $this->crearConsejo(['titulo' => 'Consejo de dieta', 'categoria' => 'dieta']);
        $this->crearConsejo(['titulo' => 'Consejo oculto de sueno', 'categoria' => 'sueno', 'publicado' => false]);
        $admin = $this->usuarioConRol(User::ROL_ADMIN);

        $this->actingAs($admin)->get(route('consejos.index', ['categoria' => 'dieta']))
            ->assertSee('Consejo de dieta')->assertDontSee('Consejo oculto de sueno');

        $this->actingAs($admin)->get(route('consejos.index', ['estado' => 'oculto']))
            ->assertSee('Consejo oculto de sueno')->assertDontSee('Consejo de dieta');

        $this->actingAs($admin)->get(route('consejos.index', ['buscar' => 'dieta']))
            ->assertSee('Consejo de dieta')->assertDontSee('Consejo oculto de sueno');
    }

    public function test_filtro_invalido_se_rechaza(): void
    {
        $this->actingAs($this->usuarioConRol(User::ROL_ADMIN))
            ->get(route('consejos.index', ['categoria' => 'otra']))
            ->assertSessionHasErrors('categoria');
    }

    public function test_el_contenido_se_escapa(): void
    {
        $this->crearConsejo(['titulo' => '<script>alert(1)</script>']);

        $this->actingAs($this->usuarioConRol(User::ROL_ADMIN))
            ->get(route('consejos.index'))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_no_se_asigna_autor_ni_vistas_por_asignacion_masiva(): void
    {
        $admin = $this->usuarioConRol(User::ROL_ADMIN);
        $otro = $this->usuarioConRol(User::ROL_USUARIO);

        $this->actingAs($admin)->post(route('consejos.store'), $this->datos(['user_id' => $otro->id, 'vistas' => 999]));

        $this->assertDatabaseHas('consejos', ['titulo' => 'Toma agua durante el día', 'user_id' => $admin->id, 'vistas' => 0]);
    }

    public function test_el_menu_de_admin_muestra_consejos_y_el_del_moderador_no(): void
    {
        $this->actingAs($this->usuarioConRol(User::ROL_ADMIN))->get(route('home'))->assertSee('Consejos');
        $this->actingAs($this->usuarioConRol(User::ROL_MODERADOR))->get(route('home'))->assertDontSee('>Consejos<', false);
    }
}