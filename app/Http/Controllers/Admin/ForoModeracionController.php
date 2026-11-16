<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForoComentario;
use App\Models\ForoPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Moderación del foro: ver, ocultar/mostrar y eliminar publicaciones y comentarios. */
class ForoModeracionController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'categoria' => ['nullable', 'in:'.implode(',', ForoPost::CATEGORIAS)],
            'estado'    => ['nullable', 'in:visible,oculto'],
            'buscar'    => ['nullable', 'string', 'max:100'],
        ]);
        $filtros = array_filter($filtros, fn ($v) => $v !== null && $v !== '');

        $posts = ForoPost::with('autor:id,name')
            ->withCount('comentarios')
            ->filtrar($filtros)
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.foro.index', [
            'posts'      => $posts,
            'filtros'    => $filtros,
            'categorias' => ForoPost::CATEGORIAS,
        ]);
    }

    public function show(ForoPost $post): View
    {
        $post->load([
            'autor:id,name,email',
            'comentarios' => fn ($q) => $q->with('autor:id,name')->oldest('id'),
        ]);

        return view('admin.foro.show', ['post' => $post]);
    }

    public function alternar(ForoPost $post): RedirectResponse
    {
        $post->oculto = ! $post->oculto;
        $post->save();

        return back()->with('success', $post->oculto
            ? 'La publicación quedó oculta y ya no se muestra en la app.'
            : 'La publicación volvió a mostrarse en la app.');
    }

    public function destroy(ForoPost $post): RedirectResponse
    {
        $post->delete(); // sus comentarios se eliminan en cascada

        return redirect()->route('foro.index')->with('success', 'Publicación eliminada.');
    }

    public function alternarComentario(ForoComentario $comentario): RedirectResponse
    {
        $comentario->oculto = ! $comentario->oculto;
        $comentario->save();

        return back()->with('success', $comentario->oculto
            ? 'El comentario quedó oculto.'
            : 'El comentario volvió a mostrarse.');
    }

    public function eliminarComentario(ForoComentario $comentario): RedirectResponse
    {
        $comentario->delete();

        return back()->with('success', 'Comentario eliminado.');
    }
}
