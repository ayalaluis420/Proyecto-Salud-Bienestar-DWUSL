<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConsejoRequest;
use App\Models\Consejo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** CRUD de consejos de salud (dieta, sueño y bienestar). Solo administradores. */
class ConsejoController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'categoria' => ['nullable', 'in:'.implode(',', array_keys(Consejo::CATEGORIAS))],
            'estado'    => ['nullable', 'in:publicado,oculto'],
            'buscar'    => ['nullable', 'string', 'max:100'],
        ]);
        $filtros = array_filter($filtros, fn ($v) => $v !== null && $v !== '');

        $consejos = Consejo::filtrar($filtros)->latest('id')->paginate(10)->withQueryString();

        return view('admin.consejos.index', [
            'consejos'   => $consejos,
            'filtros'    => $filtros,
            'categorias' => Consejo::CATEGORIAS,
        ]);
    }

    public function create(): View
    {
        return view('admin.consejos.create', ['categorias' => Consejo::CATEGORIAS]);
    }

    public function store(ConsejoRequest $request): RedirectResponse
    {
        $consejo = new Consejo($request->safe()->only(['titulo', 'categoria', 'contenido']));
        $consejo->publicado = $request->boolean('publicado');
        $consejo->user_id = $request->user()->id;
        $consejo->save();

        return redirect()->route('consejos.index')->with('success', 'Consejo creado.');
    }

    public function edit(Consejo $consejo): View
    {
        return view('admin.consejos.edit', ['consejo' => $consejo, 'categorias' => Consejo::CATEGORIAS]);
    }

    public function update(ConsejoRequest $request, Consejo $consejo): RedirectResponse
    {
        $consejo->fill($request->safe()->only(['titulo', 'categoria', 'contenido']));
        $consejo->publicado = $request->boolean('publicado');
        $consejo->save();

        return redirect()->route('consejos.index')->with('success', 'Consejo actualizado.');
    }

    public function toggle(Consejo $consejo): RedirectResponse
    {
        $consejo->publicado = ! $consejo->publicado;
        $consejo->save();

        return back()->with('success', $consejo->publicado
            ? 'El consejo quedó publicado y se muestra en la app.'
            : 'El consejo quedó oculto y ya no se muestra en la app.');
    }

    public function destroy(Consejo $consejo): RedirectResponse
    {
        $consejo->delete();

        return redirect()->route('consejos.index')->with('success', 'Consejo eliminado.');
    }
}