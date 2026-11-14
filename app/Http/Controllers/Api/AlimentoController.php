<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Planificacion\FiltroRequest;
use App\Models\Alimento;

class AlimentoController extends Controller
{
    public function index(FiltroRequest $r)
    {
        $q = Alimento::query();
        if ($r->filled('categoria')) {
            $q->where('categoria', $r->validated('categoria'));
        }
        if ($r->filled('buscar')) {
            $q->where('nombre', 'like', '%'.addcslashes($r->validated('buscar'), '\\%_').'%');
        }

        return response()->json(['success' => true, 'message' => 'Catálogo de alimentos.', 'data' => $q->orderBy('nombre')->paginate(20)]);
    }

    public function show(Alimento $alimento)
    {
        return response()->json(['success' => true, 'message' => 'Alimento.', 'data' => $alimento]);
    }
}
