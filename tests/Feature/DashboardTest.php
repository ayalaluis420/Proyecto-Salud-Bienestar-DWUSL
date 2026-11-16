<?php

namespace Tests\Feature;

use App\Models\Consejo;
use App\Models\ForoPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function crearPost(User $autor, string $titulo, bool $oculto = false): ForoPost
    {
        $p = new ForoPost(['categoria' => 'Dieta', 'titulo' => $titulo, 'contenido' => 'Contenido de prueba']);
        $p->user_id = $autor->id;
        $p->oculto = $oculto;
        $p->save();

        return $p;
    }

    public function test_el_dashboard_carga_sin_datos(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Todavía no hay publicaciones en el foro.')
            ->assertSee('Aún no hay consultas registradas desde la app.');
    }

    public function test_el_dashboard_resume_los_modulos(): void
    {
        $autor = User::factory()->usuario()->create();
        User::factory()->usuario()->inactivo()->create();
        $this->crearPost($autor, 'Publicacion reciente');
        $this->crearPost($autor, 'Publicacion moderada', true);

        $consejo = new Consejo(['titulo' => 'Consejo popular', 'categoria' => 'dieta', 'contenido' => 'Contenido del consejo de prueba.']);
        $consejo->publicado = true;
        $consejo->vistas = 7;
        $consejo->save();

        $res = $this->actingAs(User::factory()->admin()->create())->get(route('home'))->assertOk();

        $res->assertViewHas('resumen', fn (array $r) => $r['usuarios'] === 2
            && $r['usuarios_activos'] === 1
            && $r['posts'] === 2
            && $r['posts_ocultos'] === 1
            && $r['consejos_public'] === 1);

        $res->assertViewHas('actividad', fn (array $a) => count($a['etiquetas']) === 7 && end($a['posts']) === 2);
        $res->assertViewHas('categorias', fn (array $c) => $c['totales'][array_search('Dieta', $c['etiquetas'])] === 2);
        $res->assertSee('Publicacion reciente')->assertSee('Consejo popular');
    }

    public function test_el_moderador_ve_el_dashboard_sin_enlaces_de_admin(): void
    {
        $this->actingAs(User::factory()->moderador()->create())
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Moderar el foro')
            ->assertDontSee('Gestionar consejos');
    }

    public function test_usuario_de_la_app_no_ve_el_dashboard(): void
    {
        $this->actingAs(User::factory()->usuario()->create())->get(route('home'))->assertForbidden();
    }
}