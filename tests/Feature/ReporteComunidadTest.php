<?php

namespace Tests\Feature;

use App\Models\Consejo;
use App\Models\ForoComentario;
use App\Models\ForoPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ReporteComunidadTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function crearPost(string $titulo, string $categoria = 'Dieta', bool $oculto = false, ?string $fecha = null): ForoPost
    {
        $p = new ForoPost(['categoria' => $categoria, 'titulo' => $titulo, 'contenido' => 'Contenido de prueba']);
        $p->user_id = User::factory()->usuario()->create()->id;
        $p->oculto = $oculto;
        if ($fecha) {
            $p->created_at = $fecha;
        }
        $p->save();

        return $p;
    }

    private function crearConsejo(string $titulo, int $vistas, bool $publicado = true): Consejo
    {
        $c = new Consejo(['titulo' => $titulo, 'categoria' => 'sueno', 'contenido' => 'Contenido del consejo de prueba.']);
        $c->publicado = $publicado;
        $c->vistas = $vistas;
        $c->save();

        return $c;
    }

    public function test_solo_el_admin_accede_al_reporte(): void
    {
        $this->get(route('reportes.comunidad'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->moderador()->create())
            ->get(route('reportes.comunidad'))->assertForbidden();
        $this->actingAs(User::factory()->moderador()->create())
            ->get(route('reportes.comunidad.descargar', 'pdf'))->assertForbidden();

        $this->actingAs($this->admin())->get(route('reportes.comunidad'))->assertOk();
    }

    public function test_la_vista_previa_resume_y_ordena(): void
    {
        $post = $this->crearPost('Publicacion visible');
        $this->crearPost('Publicacion oculta', 'Sueño', true);
        $comentario = new ForoComentario(['contenido' => 'Un comentario']);
        $comentario->foro_post_id = $post->id;
        $comentario->user_id = $post->user_id;
        $comentario->save();

        $this->crearConsejo('Poco visto', 3);
        $this->crearConsejo('Muy visto', 40);
        $this->crearConsejo('Sin vistas', 0);

        $res = $this->actingAs($this->admin())->get(route('reportes.comunidad'))->assertOk();

        $res->assertViewHas('reporte', function (array $r) {
            return $r['resumen'] === ['publicaciones' => 2, 'visibles' => 1, 'ocultas' => 1, 'comentarios' => 1]
                && array_column($r['consejos'], 'titulo') === ['Muy visto', 'Poco visto']
                && $r['consejos'][0]['posicion'] === 1;
        });
        $res->assertSee('Publicacion visible')->assertSee('Muy visto')->assertDontSee('Sin vistas');
    }

    public function test_filtros_por_fecha_y_categoria(): void
    {
        $this->crearPost('Publicacion antigua', 'Dieta', false, '2026-01-10 10:00:00');
        $this->crearPost('Publicacion de marzo', 'Sueño', false, '2026-03-15 10:00:00');
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('reportes.comunidad', ['desde' => '2026-03-01', 'hasta' => '2026-03-31']))
            ->assertSee('Publicacion de marzo')->assertDontSee('Publicacion antigua')
            ->assertSee('Del 01/03/2026 al 31/03/2026');

        $this->actingAs($admin)->get(route('reportes.comunidad', ['categoria' => 'Dieta']))
            ->assertSee('Publicacion antigua')->assertDontSee('Publicacion de marzo');
    }

    public function test_filtros_invalidos_se_rechazan(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('reportes.comunidad', ['desde' => '15/03/2026']))
            ->assertSessionHasErrors('desde');
        $this->actingAs($admin)->get(route('reportes.comunidad', ['desde' => '2026-03-31', 'hasta' => '2026-03-01']))
            ->assertSessionHasErrors('hasta');
        $this->actingAs($admin)->get(route('reportes.comunidad.descargar', ['formato' => 'pdf', 'categoria' => 'Inventada']))
            ->assertSessionHasErrors('categoria');
    }

    public function test_formato_desconocido_da_404(): void
    {
        $this->actingAs($this->admin())->get('/admin/reportes/comunidad/csv')->assertNotFound();
    }

    public function test_descarga_pdf(): void
    {
        $this->crearPost('Publicacion visible');
        $this->crearConsejo('Muy visto', 40);

        $res = $this->actingAs($this->admin())->get(route('reportes.comunidad.descargar', 'pdf'));

        $res->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $res->getContent());
        $this->assertStringContainsString('.pdf', $res->headers->get('content-disposition'));
    }

    public function test_descarga_excel_con_dos_hojas_y_sin_formulas(): void
    {
        $this->crearPost('=1+1 titulo malicioso');
        $this->crearPost('Publicacion de sueno', 'Sueño');
        $this->crearConsejo('Muy visto', 40);

        $res = $this->actingAs($this->admin())
            ->get(route('reportes.comunidad.descargar', ['formato' => 'excel', 'categoria' => 'Dieta']));

        $res->assertOk();
        $this->assertStringContainsString('.xlsx', $res->headers->get('content-disposition'));

        $libro = IOFactory::load($res->baseResponse->getFile()->getPathname());
        $this->assertSame(['Publicaciones del foro', 'Consejos más consultados'], $libro->getSheetNames());

        $foro = $libro->getSheet(0);
        $this->assertSame('ID', $foro->getCell('A5')->getValue());
        $this->assertSame('=1+1 titulo malicioso', $foro->getCell('B6')->getValue());
        $this->assertSame('s', $foro->getCell('B6')->getDataType()); // texto, no fórmula
        $this->assertNull($foro->getCell('B7')->getValue());         // el filtro de categoría se aplicó

        $consejos = $libro->getSheet(1);
        $this->assertSame('Muy visto', $consejos->getCell('B4')->getValue());
        $this->assertEquals(40, $consejos->getCell('E4')->getValue());
    }

    public function test_el_menu_muestra_el_reporte_solo_al_admin(): void
    {
        $this->actingAs($this->admin())->get(route('home'))->assertSee('Reporte de comunidad');
        $this->actingAs(User::factory()->moderador()->create())->get(route('home'))->assertDontSee('Reporte de comunidad');
    }
}