<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Planificacion\FiltroRequest;
use App\Http\Requests\Planificacion\PlanComidaRequest;
use App\Models\Alimento;
use App\Models\PlanComida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanComidaController extends Controller
{
    public function index(FiltroRequest $r)
    {
        $q = $r->user()->planesComida()->with('detalles.alimento');
        if ($r->filled('fecha')) {
            $q->whereDate('fecha', $r->validated('fecha'));
        }
        if ($r->filled('tipo')) {
            $q->where('tipo', $r->validated('tipo'));
        }

        return response()->json(['success' => true, 'message' => 'Planes de comida.', 'data' => $q->orderByDesc('fecha')->paginate(20)]);
    }

    public function show(Request $r, PlanComida $plan)
    {
        abort_unless($plan->user_id === $r->user()->id, 404);

        return response()->json(['success' => true, 'message' => 'Plan de comida.', 'data' => $plan->load('detalles.alimento')]);
    }

    public function store(PlanComidaRequest $r)
    {
        return $this->guardar($r);
    }

    public function update(PlanComidaRequest $r, PlanComida $plan)
    {
        abort_unless($plan->user_id === $r->user()->id, 404);

        return $this->guardar($r, $plan);
    }

    private function guardar(PlanComidaRequest $r, ?PlanComida $plan = null)
    {
        $nuevo = $plan === null;
        $plan = DB::transaction(function () use ($r, $plan) {
            $datos = $r->safe()->only(['fecha', 'tipo', 'notas']);
            if ($plan) {
                $plan->update($datos);
            } else {
                $plan = $r->user()->planesComida()->create($datos);
            }
            $anteriores = $plan->detalles()->get()->keyBy('alimento_id');
            $alimentos = Alimento::whereIn('id', array_column($r->validated('alimentos'), 'alimento_id'))->get()->keyBy('id');
            $plan->detalles()->delete();
            foreach ($r->validated('alimentos') as $item) {
                $plan->detalles()->create([
                    'alimento_id' => $item['alimento_id'], 'porciones' => $item['porciones'],
                    'calorias_porcion' => $anteriores->get($item['alimento_id'])?->calorias_porcion ?? $alimentos[$item['alimento_id']]->calorias,
                ]);
            }

            return $plan;
        });

        return response()->json(['success' => true, 'message' => 'Plan guardado.', 'data' => $plan->load('detalles.alimento')], $nuevo ? 201 : 200);
    }

    public function destroy(Request $r, PlanComida $plan)
    {
        abort_unless($plan->user_id === $r->user()->id, 404);
        $plan->delete();

        return response()->json(['success' => true, 'message' => 'Plan eliminado.', 'data' => null]);
    }
}
