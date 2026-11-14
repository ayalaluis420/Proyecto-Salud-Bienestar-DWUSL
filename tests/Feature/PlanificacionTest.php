<?php

namespace Tests\Feature;

use App\Exports\PlanificacionExport;
use App\Models\Alimento;
use App\Models\Objetivo;
use App\Models\Recordatorio;
use App\Models\User;
use App\Services\ReportePlanificacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class PlanificacionTest extends TestCase
{
    use RefreshDatabase;

    private function objetivo(User $u, array $extra = []): Objetivo
    {
        return $u->objetivos()->create(array_merge(['titulo' => 'Caminar', 'unidad' => 'km', 'valor_meta' => 100, 'valor_actual' => 25, 'fecha_inicio' => today()->subDays(5), 'fecha_fin' => today()->addDays(5)], $extra));
    }

    private function recordatorio(User $u, array $extra = []): Recordatorio
    {
        return $u->recordatorios()->create(array_merge(['nombre' => 'Vitamina de prueba', 'tipo' => 'vitamina', 'dosis' => '1 unidad', 'hora' => '08:00', 'frecuencia' => 'diaria', 'fecha_inicio' => today()->subDays(2), 'activo' => true], $extra));
    }

    private function alimento(): Alimento
    {
        return Alimento::create(['nombre' => 'Manzana', 'categoria' => 'frutas', 'calorias' => 52, 'porcion_gramos' => 100]);
    }

    public function test_los_endpoints_requieren_autenticacion(): void
    {
        foreach (['objetivos', 'recordatorios', 'alimentos', 'planes-comida'] as $ruta) {
            $this->getJson('/api/'.$ruta)->assertUnauthorized();
        }
    }

    public function test_objetivos_crud_y_estado_calculado(): void
    {
        $u = User::factory()->usuario()->create();
        Sanctum::actingAs($u);
        $id = $this->postJson('/api/objetivos', ['titulo' => 'Caminar', 'unidad' => 'km', 'valor_meta' => 50, 'fecha_inicio' => today()->toDateString(), 'fecha_fin' => today()->addDays(5)->toDateString(), 'user_id' => 999])->assertCreated()->assertJsonPath('data.estado', 'en_curso')->json('data.id');
        $this->assertDatabaseHas('objetivos', ['id' => $id, 'user_id' => $u->id]);
        $this->putJson('/api/objetivos/'.$id, ['valor_actual' => 50])->assertOk()->assertJsonPath('data.estado', 'cumplido')->assertJsonPath('data.porcentaje', 100);
        $this->getJson('/api/objetivos?estado=cumplido')->assertOk()->assertJsonPath('data.total', 1);
        $this->getJson('/api/objetivos/'.$id)->assertOk();
        $this->deleteJson('/api/objetivos/'.$id)->assertOk();
        $this->assertDatabaseCount('objetivos', 0);
    }

    public function test_estado_vencido_y_fechas_limite_en_actualizacion_parcial(): void
    {
        $u = User::factory()->usuario()->create();
        Sanctum::actingAs($u);
        $o = $this->objetivo($u, ['fecha_fin' => today()->subDay()]);
        $this->getJson('/api/objetivos/'.$o->id)->assertOk()->assertJsonPath('data.estado', 'vencido');
        $this->putJson('/api/objetivos/'.$o->id, ['fecha_inicio' => today()->addDay()->toDateString()])->assertUnprocessable()->assertJsonValidationErrors('fecha_inicio');
        $this->putJson('/api/objetivos/'.$o->id, ['valor_meta' => 0])->assertUnprocessable();
        $this->getJson('/api/objetivos?estado=desconocido')->assertUnprocessable();
    }

    public function test_nadie_puede_leer_modificar_o_borrar_la_planificacion_ajena(): void
    {
        $dueno = User::factory()->usuario()->create();
        $otro = User::factory()->usuario()->create();
        $o = $this->objetivo($dueno);
        $r = $this->recordatorio($dueno);
        $p = $dueno->planesComida()->create(['fecha' => today(), 'tipo' => 'cena']);
        Sanctum::actingAs($otro);
        foreach (['objetivos' => $o, 'recordatorios' => $r, 'planes-comida' => $p] as $ruta => $modelo) {
            $this->getJson('/api/'.$ruta.'/'.$modelo->id)->assertNotFound();
            $this->putJson('/api/'.$ruta.'/'.$modelo->id, [])->assertNotFound();
            $this->deleteJson('/api/'.$ruta.'/'.$modelo->id)->assertNotFound();
            $this->getJson('/api/'.$ruta.'?user_id='.$dueno->id)->assertOk()->assertJsonPath('data.total', 0);
        }
        $this->postJson('/api/recordatorios/'.$r->id.'/cumplimientos', ['fecha' => today()->toDateString()])->assertNotFound();
    }

    public function test_recordatorios_crud_y_tomas_idempotentes(): void
    {
        $u = User::factory()->usuario()->create();
        Sanctum::actingAs($u);
        $id = $this->postJson('/api/recordatorios', ['nombre' => 'Ejemplo', 'tipo' => 'medicamento', 'dosis' => 'Dosis de prueba', 'hora' => '09:30', 'frecuencia' => 'diaria', 'fecha_inicio' => today()->toDateString()])->assertCreated()->json('data.id');
        $this->putJson('/api/recordatorios/'.$id, ['hora' => '10:00'])->assertOk();
        for ($i = 0; $i < 2; $i++) {
            $this->postJson('/api/recordatorios/'.$id.'/cumplimientos', ['fecha' => today()->toDateString()])->assertOk();
        }
        $this->assertDatabaseCount('recordatorio_cumplimientos', 1);
        $this->postJson('/api/recordatorios/'.$id.'/cumplimientos', ['fecha' => today()->addDay()->toDateString()])->assertUnprocessable();
        $this->putJson('/api/recordatorios/'.$id, ['frecuencia' => 'semanal'])->assertUnprocessable();
        $this->getJson('/api/recordatorios/'.$id)->assertOk()->assertJsonCount(1, 'data.cumplimientos');
        $this->deleteJson('/api/recordatorios/'.$id.'/cumplimientos', ['fecha' => today()->toDateString()])->assertOk();
        $this->assertDatabaseCount('recordatorio_cumplimientos', 0);
        $this->deleteJson('/api/recordatorios/'.$id)->assertOk();
        $this->assertDatabaseCount('recordatorios', 0);
    }

    public function test_tomas_no_programadas_pausadas_y_calendario_mensual(): void
    {
        $u = User::factory()->usuario()->create();
        Sanctum::actingAs($u);
        $r = $this->recordatorio($u, ['fecha_inicio' => '2026-01-31', 'frecuencia' => 'mensual']);
        $this->assertTrue($r->programadoEn('2026-02-28'));
        $this->assertFalse($r->programadoEn('2026-02-27'));
        $this->postJson('/api/recordatorios/'.$r->id.'/cumplimientos', ['fecha' => '2026-02-27'])->assertUnprocessable();
        $r->update(['activo' => false]);
        $this->postJson('/api/recordatorios/'.$r->id.'/cumplimientos', ['fecha' => '2026-02-28'])->assertUnprocessable();
        $this->postJson('/api/recordatorios', ['nombre' => 'X', 'tipo' => 'otro', 'hora' => '99:00', 'frecuencia' => 'cada_rato'])->assertUnprocessable()->assertJsonValidationErrors(['tipo', 'hora', 'frecuencia', 'dosis', 'fecha_inicio']);
    }

    public function test_planes_crud_calorias_validacion_y_unicidad(): void
    {
        $u = User::factory()->usuario()->create();
        Sanctum::actingAs($u);
        $a = $this->alimento();
        $datos = ['fecha' => today()->toDateString(), 'tipo' => 'almuerzo', 'alimentos' => [['alimento_id' => $a->id, 'porciones' => 2]]];
        $id = $this->postJson('/api/planes-comida', $datos)->assertCreated()->assertJsonPath('data.calorias_totales', 104)->json('data.id');
        $this->postJson('/api/planes-comida', $datos)->assertUnprocessable()->assertJsonValidationErrors('fecha');
        $a->update(['calorias' => 60]);
        $this->putJson('/api/planes-comida/'.$id, $datos)->assertOk()->assertJsonPath('data.calorias_totales', 104);
        $this->getJson('/api/planes-comida/'.$id)->assertOk()->assertJsonCount(1, 'data.detalles');
        $this->putJson('/api/planes-comida/'.$id, array_merge($datos, ['alimentos' => [['alimento_id' => 999, 'porciones' => -1]]]))->assertUnprocessable();
        $this->assertDatabaseCount('plan_comida_detalles', 1);
        $this->deleteJson('/api/planes-comida/'.$id)->assertOk();
        $this->assertDatabaseCount('plan_comida_detalles', 0);
    }

    public function test_api_alimentos_es_catalogo_de_solo_lectura(): void
    {
        Sanctum::actingAs(User::factory()->usuario()->create());
        $a = $this->alimento();
        $this->getJson('/api/alimentos?categoria=frutas')->assertOk()->assertJsonPath('data.total', 1);
        $this->getJson('/api/alimentos/'.$a->id)->assertOk()->assertJsonPath('data.nombre', 'Manzana');
        $this->postJson('/api/alimentos', [])->assertMethodNotAllowed();
    }

    public function test_permisos_del_panel_y_nombres_de_rutas_no_colisionan(): void
    {
        $moderador = User::factory()->moderador()->create();
        foreach (['objetivos', 'recordatorios', 'alimentos', 'planes-comida'] as $ruta) {
            $this->actingAs($moderador)->get('/admin/'.$ruta)->assertOk();
            $this->actingAs(User::factory()->usuario()->create())->get('/admin/'.$ruta)->assertForbidden();
        }
        $this->actingAs($moderador)->get('/admin/alimentos/create')->assertForbidden();
        $this->actingAs($moderador)->get('/admin/planificacion/reportes')->assertForbidden();
        $this->assertStringEndsWith('/admin/alimentos', route('alimentos.index'));
        $this->assertStringEndsWith('/api/alimentos', route('api.alimentos.index'));
    }

    public function test_admin_gestiona_alimentos_y_no_elimina_los_utilizados(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $datos = ['nombre' => 'Avena', 'categoria' => 'cereales', 'calorias' => 389, 'porcion_gramos' => 100];
        $this->post('/admin/alimentos', $datos)->assertRedirect(route('alimentos.index'));
        $a = Alimento::first();
        $this->get('/admin/alimentos/'.$a->id.'/edit')->assertOk();
        $this->put('/admin/alimentos/'.$a->id, array_merge($datos, ['calorias' => 380]))->assertRedirect();
        $this->post('/admin/alimentos', $datos)->assertSessionHasErrors('nombre');
        $this->delete('/admin/alimentos/'.$a->id)->assertRedirect();
        $this->assertDatabaseCount('alimentos', 0);
        $a = $this->alimento();
        $p = $admin->planesComida()->create(['fecha' => today(), 'tipo' => 'cena']);
        $p->detalles()->create(['alimento_id' => $a->id, 'porciones' => 1, 'calorias_porcion' => 52]);
        $this->delete('/admin/alimentos/'.$a->id)->assertSessionHas('error');
        $this->assertModelExists($a);
    }

    public function test_panel_muestra_datos_y_aplica_filtros(): void
    {
        $u = User::factory()->usuario()->create(['name' => 'Ana Prueba']);
        $o = $this->objetivo($u);
        $this->recordatorio($u);
        $a = $this->alimento();
        $p = $u->planesComida()->create(['fecha' => today(), 'tipo' => 'cena']);
        $p->detalles()->create(['alimento_id' => $a->id, 'porciones' => 2, 'calorias_porcion' => 52]);
        $this->actingAs(User::factory()->admin()->create());
        $this->get('/admin/objetivos?estado=en_curso')->assertOk()->assertSee('Caminar')->assertSee('Ana Prueba');
        $this->get('/admin/objetivos?estado=cumplido')->assertOk()->assertDontSee('Caminar');
        $this->get('/admin/recordatorios?tipo=vitamina')->assertOk()->assertSee('Vitamina de prueba');
        $this->get('/admin/planes-comida?fecha='.today()->toDateString())->assertOk()->assertSee('104')->assertSee('Manzana');
        $this->get('/admin/planificacion/reportes')->assertOk();
        $this->get('/admin/objetivos?desde=no-fecha&hasta=2026-12-31')->assertRedirect()->assertSessionHasErrors('desde');
    }

    public function test_reporte_calcula_cumplimiento_real_y_excluye_tomas_futuras(): void
    {
        $u = User::factory()->usuario()->create();
        $this->objetivo($u, ['valor_actual' => 100, 'fecha_fin' => today()]);
        $this->objetivo($u, ['fecha_fin' => today()->subDay()]);
        $r = $this->recordatorio($u);
        $r->cumplimientos()->create(['fecha' => today()]);
        $f = ['desde' => today()->subDays(2)->toDateString(), 'hasta' => today()->addDays(3)->toDateString()];
        $s = new ReportePlanificacion;
        $o = $s->generar('objetivos', $f);
        $this->assertSame(2, $o['esperados']);
        $this->assertSame(1, $o['cumplidos']);
        $r = $s->generar('recordatorios', $f);
        $this->assertSame(3, $r['esperados']);
        $this->assertSame(1, $r['cumplidos']);
        $f = ['desde' => today()->addDay()->toDateString(), 'hasta' => today()->addDays(3)->toDateString()];
        $this->assertSame(0, $s->generar('recordatorios', $f)['esperados']);
    }

    public function test_reportes_pdf_excel_y_periodos_invalidos(): void
    {
        $u = User::factory()->admin()->create();
        $this->objetivo($u, ['fecha_fin' => today()]);
        $this->recordatorio($u);
        $this->actingAs($u);
        foreach (['objetivos', 'recordatorios'] as $tipo) {
            $this->get('/admin/planificacion/reportes/'.$tipo.'/pdf')->assertOk()->assertHeader('content-type', 'application/pdf');
            $this->get('/admin/planificacion/reportes/'.$tipo.'/excel')->assertOk();
        }
        $this->get('/admin/planificacion/reportes/objetivos/pdf?desde=2020-01-01&hasta=2026-01-01')->assertRedirect()->assertSessionHasErrors('hasta');
        $this->get('/admin/planificacion/reportes/recordatorios/pdf?desde=2027-01-01&hasta=2026-01-01')->assertRedirect()->assertSessionHasErrors('hasta');
    }

    public function test_fechas_con_estructura_invalida_devuelven_422(): void
    {
        $u = User::factory()->usuario()->create();
        Sanctum::actingAs($u);
        $this->postJson('/api/objetivos', ['fecha_inicio' => ['x'], 'fecha_fin' => ['x']])->assertUnprocessable();
        $this->postJson('/api/recordatorios', ['fecha_inicio' => ['x'], 'fecha_fin' => ['x']])->assertUnprocessable();
        $this->postJson('/api/planes-comida', ['fecha' => today()->toDateString(), 'tipo' => ['x']])->assertUnprocessable();
        $this->postJson('/api/objetivos', ['titulo' => 'Meta', 'unidad' => 'km', 'valor_meta' => 0.001, 'fecha_inicio' => today()->toDateString(), 'fecha_fin' => today()->toDateString()])->assertUnprocessable()->assertJsonValidationErrors('valor_meta');
    }

    public function test_excel_no_interpreta_nombres_como_formulas(): void
    {
        $r = ['encabezados' => ['Nombre'], 'filas' => [['=1+1']]];
        $libro = new Spreadsheet;
        (new PlanificacionExport($r))->bindValue($libro->getActiveSheet()->getCell('A1'), '=1+1');
        $this->assertSame('s', $libro->getActiveSheet()->getCell('A1')->getDataType());
    }
}
