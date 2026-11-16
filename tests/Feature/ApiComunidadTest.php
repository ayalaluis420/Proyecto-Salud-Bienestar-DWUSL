<?php

namespace Tests\Feature;

use App\Models\Consejo;
use App\Models\ForoComentario;
use App\Models\ForoPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiComunidadTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(): User
    {
        return User::factory()->usuario()->create();
    }

    private function crearConsejo(string $titulo, string $categoria = 'dieta', bool $publicado = true): Consejo
    {
        $c = new Consejo(['titulo' => $titulo, 'categoria' => $categoria, 'contenido' => 'Contenido del consejo de prueba.']);
        $c->publicado = $publicado;
        $c->save();

        return $c;
    }

    private function crearPost(User $autor, string $titulo = 'Titulo de prueba', bool $oculto = false): ForoPost
    {
        $p = new ForoPost(['categoria' => 'Dieta', 'titulo' => $titulo, 'contenido' => 'Contenido de prueba']);
        $p->user_id = $autor->id;
        $p->oculto = $oculto;
        $p->save();

        return $p;
    }

    private function crearComentario(ForoPost $post, User $autor, string $texto = 'Un comentario', bool $oculto = false): ForoComentario
    {
        $c = new ForoComentario(['contenido' => $texto]);
        $c->foro_post_id = $post->id;
        $c->user_id = $autor->id;
        $c->oculto = $oculto;
        $c->save();

        return $c;
    }

    // ------------------------------------------------------------ Autenticación

    public function test_los_endpoints_requieren_token(): void
    {
        $this->getJson('/api/consejos')->assertUnauthorized();
        $this->getJson('/api/foro')->assertUnauthorized();
        $this->postJson('/api/foro', [])->assertUnauthorized();
    }

    public function test_cuenta_desactivada_no_accede(): void
    {
        Sanctum::actingAs(User::factory()->usuario()->inactivo()->create());

        $this->getJson('/api/foro')->assertForbidden();
    }

    // ----------------------------------------------------------------- Consejos

    public function test_lista_solo_consejos_publicados_y_filtra_por_categoria(): void
    {
        $this->crearConsejo('Consejo de dieta', 'dieta');
        $this->crearConsejo('Consejo de sueno', 'sueno');
        $this->crearConsejo('Consejo oculto', 'dieta', false);
        Sanctum::actingAs($this->usuario());

        $this->getJson('/api/consejos')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.data')
            ->assertJsonMissing(['titulo' => 'Consejo oculto']);

        $this->getJson('/api/consejos?categoria=sueno')
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.titulo', 'Consejo de sueno')
            ->assertJsonPath('data.data.0.categoria_nombre', 'Sueño');

        $this->getJson('/api/consejos?categoria=otra')->assertStatus(422);
    }

    public function test_ver_un_consejo_suma_una_vista(): void
    {
        $consejo = $this->crearConsejo('Consejo de dieta');
        Sanctum::actingAs($this->usuario());

        $this->getJson('/api/consejos/'.$consejo->id)
            ->assertOk()
            ->assertJsonPath('data.titulo', 'Consejo de dieta')
            ->assertJsonPath('data.vistas', 1);

        $this->getJson('/api/consejos/'.$consejo->id)->assertJsonPath('data.vistas', 2);
        $this->assertSame(2, $consejo->fresh()->vistas);
    }

    public function test_consejo_oculto_o_inexistente_da_404(): void
    {
        $oculto = $this->crearConsejo('Consejo oculto', 'dieta', false);
        Sanctum::actingAs($this->usuario());

        $this->getJson('/api/consejos/'.$oculto->id)->assertNotFound();
        $this->getJson('/api/consejos/9999')->assertNotFound();
        $this->assertSame(0, $oculto->fresh()->vistas);
    }

    // --------------------------------------------------------------------- Foro

    public function test_lista_publicaciones_visibles_sin_exponer_correos(): void
    {
        $autor = $this->usuario();
        $post = $this->crearPost($autor, 'Publicacion visible');
        $this->crearComentario($post, $autor);
        $this->crearComentario($post, $autor, 'Comentario oculto', true);
        $this->crearPost($autor, 'Publicacion oculta', true);

        Sanctum::actingAs($this->usuario());

        $res = $this->getJson('/api/foro')
            ->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.titulo', 'Publicacion visible')
            ->assertJsonPath('data.data.0.autor', $autor->name)
            ->assertJsonPath('data.data.0.es_mio', false)
            ->assertJsonPath('data.data.0.comentarios_count', 1);

        $this->assertStringNotContainsString($autor->email, $res->getContent());
    }

    public function test_publicar_usa_al_usuario_del_token_como_autor(): void
    {
        $yo = $this->usuario();
        $otro = $this->usuario();
        Sanctum::actingAs($yo);

        $this->postJson('/api/foro', [
            'categoria' => 'Ejercicio',
            'titulo'    => 'Mi rutina',
            'contenido' => 'Comparto mi rutina de la semana.',
            'user_id'   => $otro->id,   // intento de suplantar: se ignora
            'oculto'    => true,        // tampoco se puede fijar desde la app
        ])
            ->assertCreated()
            ->assertJsonPath('data.titulo', 'Mi rutina')
            ->assertJsonPath('data.es_mio', true);

        $this->assertDatabaseHas('foro_posts', ['titulo' => 'Mi rutina', 'user_id' => $yo->id, 'oculto' => 0]);
    }

    public function test_publicar_valida_los_datos(): void
    {
        Sanctum::actingAs($this->usuario());

        $this->postJson('/api/foro', ['categoria' => 'Inventada', 'titulo' => 'a', 'contenido' => 'b'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['categoria', 'titulo', 'contenido']);

        $this->postJson('/api/foro', ['categoria' => 'Dieta', 'titulo' => str_repeat('a', 81), 'contenido' => str_repeat('b', 1001)])
            ->assertJsonValidationErrors(['titulo', 'contenido']);

        $this->assertDatabaseCount('foro_posts', 0);
    }

    public function test_ver_publicacion_incluye_solo_comentarios_visibles(): void
    {
        $autor = $this->usuario();
        $post = $this->crearPost($autor);
        $this->crearComentario($post, $autor, 'Comentario visible');
        $this->crearComentario($post, $autor, 'Comentario oculto', true);

        Sanctum::actingAs($autor);

        $this->getJson('/api/foro/'.$post->id)
            ->assertOk()
            ->assertJsonPath('data.es_mio', true)
            ->assertJsonPath('data.comentarios_count', 1)
            ->assertJsonCount(1, 'data.comentarios')
            ->assertJsonPath('data.comentarios.0.contenido', 'Comentario visible');
    }

    public function test_publicacion_oculta_no_se_puede_ver_ni_comentar(): void
    {
        $autor = $this->usuario();
        $oculto = $this->crearPost($autor, 'Oculta', true);
        Sanctum::actingAs($this->usuario());

        $this->getJson('/api/foro/'.$oculto->id)->assertNotFound();
        $this->postJson('/api/foro/'.$oculto->id.'/comentarios', ['contenido' => 'Hola a todos'])->assertNotFound();
        $this->assertDatabaseCount('foro_comentarios', 0);
    }

    public function test_comentar_una_publicacion(): void
    {
        $post = $this->crearPost($this->usuario());
        $yo = $this->usuario();
        Sanctum::actingAs($yo);

        $this->postJson('/api/foro/'.$post->id.'/comentarios', ['contenido' => 'Muy buen aporte'])
            ->assertCreated()
            ->assertJsonPath('data.contenido', 'Muy buen aporte')
            ->assertJsonPath('data.es_mio', true);

        $this->assertDatabaseHas('foro_comentarios', ['foro_post_id' => $post->id, 'user_id' => $yo->id]);

        $this->postJson('/api/foro/'.$post->id.'/comentarios', ['contenido' => 'a'])
            ->assertJsonValidationErrors('contenido');
        $this->postJson('/api/foro/'.$post->id.'/comentarios', ['contenido' => str_repeat('a', 501)])
            ->assertJsonValidationErrors('contenido');
    }

    public function test_solo_el_autor_borra_su_publicacion(): void
    {
        $autor = $this->usuario();
        $post = $this->crearPost($autor);

        Sanctum::actingAs($this->usuario());
        $this->deleteJson('/api/foro/'.$post->id)->assertNotFound();
        $this->assertDatabaseHas('foro_posts', ['id' => $post->id]);

        Sanctum::actingAs($autor);
        $this->deleteJson('/api/foro/'.$post->id)->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseMissing('foro_posts', ['id' => $post->id]);
    }

    public function test_solo_el_autor_borra_su_comentario(): void
    {
        $autor = $this->usuario();
        $comentario = $this->crearComentario($this->crearPost($autor), $autor);

        Sanctum::actingAs($this->usuario());
        $this->deleteJson('/api/foro/comentarios/'.$comentario->id)->assertNotFound();

        Sanctum::actingAs($autor);
        $this->deleteJson('/api/foro/comentarios/'.$comentario->id)->assertOk();
        $this->assertDatabaseMissing('foro_comentarios', ['id' => $comentario->id]);
    }

    public function test_lo_publicado_en_la_app_aparece_en_el_panel_de_moderacion(): void
    {
        Sanctum::actingAs($this->usuario());
        $this->postJson('/api/foro', ['categoria' => 'Bienestar', 'titulo' => 'Desde la app', 'contenido' => 'Publicado desde el telefono.'])
            ->assertCreated();

        $mod = User::factory()->moderador()->create();
        $this->actingAs($mod, 'web')->get(route('foro.index'))->assertOk()->assertSee('Desde la app');
    }
}