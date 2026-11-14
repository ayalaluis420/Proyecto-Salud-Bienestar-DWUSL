<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Planificacion\AlimentoRequest;
use App\Http\Requests\Planificacion\FiltroRequest;
use App\Models\Alimento;
use Illuminate\Database\QueryException;

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

        return view('admin.alimentos.index', ['alimentos' => $q->orderBy('nombre')->paginate(15)->withQueryString(), 'filtros' => $r->validated(), 'categorias' => Alimento::CATEGORIAS]);
    }

    public function create()
    {
        return view('admin.alimentos.form', ['alimento' => new Alimento, 'categorias' => Alimento::CATEGORIAS]);
    }

    public function store(AlimentoRequest $r)
    {
        Alimento::create($r->validated());

        return to_route('alimentos.index')->with('success', 'Alimento creado correctamente.');
    }

    public function edit(Alimento $alimento)
    {
        return view('admin.alimentos.form', ['alimento' => $alimento, 'categorias' => Alimento::CATEGORIAS]);
    }

    public function update(AlimentoRequest $r, Alimento $alimento)
    {
        $alimento->update($r->validated());

        return to_route('alimentos.index')->with('success', 'Alimento actualizado correctamente.');
    }

    public function destroy(Alimento $alimento)
    {
        if ($alimento->detalles()->exists()) {
            return back()->with('error', 'Este alimento está incluido en planes de comida. No se puede eliminar.');
        }
        try {
            $alimento->delete();
        } catch (QueryException $e) {
            if (in_array((string) $e->getCode(), ['23000', '23503'])) {
                return back()->with('error', 'El alimento ya está incluido en un plan de comida.');
            }
            throw $e;
        }

        return to_route('alimentos.index')->with('success', 'Alimento eliminado correctamente.');
    }
}
