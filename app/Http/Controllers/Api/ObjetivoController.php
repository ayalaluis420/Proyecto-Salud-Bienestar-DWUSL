<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Planificacion\FiltroRequest;
use App\Http\Requests\Planificacion\ObjetivoRequest;
use App\Models\Objetivo;
use Illuminate\Http\Request;

class ObjetivoController extends Controller
{
    public function index(FiltroRequest $r)
    {
        $data = $r->user()->objetivos()->estado($r->validated('estado'))->latest('id')->paginate(20);

        return response()->json(['success' => true, 'message' => 'Objetivos de salud.', 'data' => $data]);
    }

    public function store(ObjetivoRequest $r)
    {
        return response()->json(['success' => true, 'message' => 'Objetivo creado.', 'data' => $r->user()->objetivos()->create($r->validated())->refresh()], 201);
    }

    public function show(Request $r, Objetivo $objetivo)
    {
        abort_unless($objetivo->user_id === $r->user()->id, 404);

        return response()->json(['success' => true, 'message' => 'Objetivo de salud.', 'data' => $objetivo]);
    }

    public function update(ObjetivoRequest $r, Objetivo $objetivo)
    {
        abort_unless($objetivo->user_id === $r->user()->id, 404);
        $objetivo->update($r->validated());

        return response()->json(['success' => true, 'message' => 'Objetivo actualizado.', 'data' => $objetivo->refresh()]);
    }

    public function destroy(Request $r, Objetivo $objetivo)
    {
        abort_unless($objetivo->user_id === $r->user()->id, 404);
        $objetivo->delete();

        return response()->json(['success' => true, 'message' => 'Objetivo eliminado.', 'data' => null]);
    }
}
