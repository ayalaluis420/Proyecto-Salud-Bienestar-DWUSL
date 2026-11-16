<?php

namespace Tests\Feature;

use App\Models\ForoComentario;
use App\Models\ForoPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForoModeracionTest extends TestCase
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

    private function crearPost(User $autor, bool $oculto = false): ForoPost
    {
        $p = new ForoPost(['categoria' => 'Dieta', 'titulo' => 'Titulo de prueba', 'contenido' => 'Contenido de prueba']);
        $p->user_id = $autor->id;
        $p->oculto = $oculto;
        $p->save();

        return $p;
    }

    private function crearComentario(ForoPost $post, User $autor): ForoComentario
    {
        $c = new ForoComentario(['contenido' => 'Un comentario']);
        $c->foro_post_id = $post->id;
        $c->user_id = $autor->id;
        $c->save();

        return $c;
    }

    public function test_invitado_es_enviado_al_login(): void
    {
        $this->get(route('foro.index'))->assertRedirect(route('login'));
    }

    public function test_usuario_de_la_app_no_entra_al_panel_del_foro(): void
    {
        $usuario = $this->usuarioConRol(User::ROL_USUARIO);
        $this->actingAs($usuario)->get(route('foro.index'))->assertForbidden();
    }

    public function test_moderador_y_admin_ven_el_listado(): void
    {
        $autor = $this->usuarioConRol(User::ROL_USUARIO);
        $this->crearPost($autor);

        foreach ([User::ROL_MODERADOR, User::ROL_ADMIN] as $rol) {
            $this->actingAs($this->usuarioConRol($rol))
                ->get(route('foro.index'))
                ->assertOk()
                ->assertSee('Titulo de prueba');
        }
    }

    public function test_filtros_por_estado_y_busqueda(): void
    {
        $autor = $this->usuarioConRol(User::ROL_USUARIO);
        $this->crearPost($autor);
        $oculto = $this->crearPost($autor, true);
        $oculto->titulo = 'Publicacion oculta';
        $oculto->save();

        $mod = $this->usuarioConRol(User::ROL_MODERADOR);

        $this->actingAs($mod)->get(route('foro.index', ['estado' => 'oculto']))
            ->assertSee('Publicacion oculta')->assertDontSee('Titulo de prueba');

        $this->actingAs($mod)->get(route('foro.index', ['buscar' => 'Titulo']))
            ->assertSee('Titulo de prueba')->assertDontSee('Publicacion oculta');
    }

    public function test_filtro_invalido_se_rechaza(): void
    {
        $mod = $this->usuarioConRol(User::ROL_MODERADOR);
        $this->actingAs($mod)->get(route('foro.index', ['estado' => 'cualquiera']))
            ->assertSessionHasErrors('estado');
    }

    public function test_ver_publicacion_con_comentarios(): void
    {
        $autor = $this->usuarioConRol(User::ROL_USUARIO);
        $post = $this->crearPost($autor);
        $this->crearComentario($post, $autor);

        $this->actingAs($this->usuarioConRol(User::ROL_MODERADOR))
            ->get(route('foro.show', $post))
            ->assertOk()
            ->assertSee('Un comentario');
    }

    public function test_el_contenido_se_escapa_en_la_vista(): void
    {
        $autor = $this->usuarioConRol(User::ROL_USUARIO);
        $post = $this->crearPost($autor);
        $post->contenido = '<script>alert(1)</script>';
        $post->save();

        $this->actingAs($this->usuarioConRol(User::ROL_MODERADOR))
            ->get(route('foro.show', $post))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_ocultar_y_mostrar_publicacion(): void
    {
        $post = $this->crearPost($this->usuarioConRol(User::ROL_USUARIO));
        $mod = $this->usuarioConRol(User::ROL_MODERADOR);

        $this->actingAs($mod)->patch(route('foro.visibilidad', $post))->assertSessionHas('success');
        $this->assertTrue($post->fresh()->oculto);

        $this->actingAs($mod)->patch(route('foro.visibilidad', $post));
        $this->assertFalse($post->fresh()->oculto);
    }

    public function test_eliminar_publicacion_elimina_sus_comentarios(): void
    {
        $autor = $this->usuarioConRol(User::ROL_USUARIO);
        $post = $this->crearPost($autor);
        $c = $this->crearComentario($post, $autor);

        $this->actingAs($this->usuarioConRol(User::ROL_MODERADOR))
            ->delete(route('foro.destroy', $post))
            ->assertRedirect(route('foro.index'));

        $this->assertDatabaseMissing('foro_posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('foro_comentarios', ['id' => $c->id]);
    }

    public function test_ocultar_y_eliminar_comentario(): void
    {
        $autor = $this->usuarioConRol(User::ROL_USUARIO);
        $post = $this->crearPost($autor);
        $c = $this->crearComentario($post, $autor);
        $mod = $this->usuarioConRol(User::ROL_MODERADOR);

        $this->actingAs($mod)->patch(route('foro.comentarios.visibilidad', $c));
        $this->assertTrue($c->fresh()->oculto);

        $this->actingAs($mod)->delete(route('foro.comentarios.destroy', $c));
        $this->assertDatabaseMissing('foro_comentarios', ['id' => $c->id]);
    }

    public function test_usuario_de_la_app_no_puede_moderar(): void
    {
        $autor = $this->usuarioConRol(User::ROL_USUARIO);
        $post = $this->crearPost($autor);

        $this->actingAs($autor)->patch(route('foro.visibilidad', $post))->assertForbidden();
        $this->actingAs($autor)->delete(route('foro.destroy', $post))->assertForbidden();
        $this->assertDatabaseHas('foro_posts', ['id' => $post->id]);
    }

    public function test_el_menu_muestra_el_enlace_de_moderacion(): void
    {
        $this->actingAs($this->usuarioConRol(User::ROL_MODERADOR))
            ->get(route('home'))
            ->assertSee('Moderación del foro');
    }
}