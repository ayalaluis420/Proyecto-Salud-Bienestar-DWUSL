<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ForoComentario;
use App\Models\ForoPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Foro de salud para la app: listar, publicar, comentar y borrar lo propio.
 * Lo que un moderador oculta en el panel deja de existir para la app (404).
 */
class ForoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'categoria' => ['nullable', Rule::in(ForoPost::CATEGORIAS)],
            'buscar'    => ['nullable', 'string', 'max:100'],
        ]);

        $uid = $request->user()->id;

        $posts = ForoPost::visibles()
            ->filtrar($filtros)
            ->with('autor:id,name')
            ->withCount(['comentarios' => fn ($q) => $q->visibles()])
            ->latest('id')
            ->paginate(20)
            ->through(fn (ForoPost $p) => $this->formatoPost($p, $uid));

        return response()->json(['success' => true, 'message' => 'Publicaciones del foro.', 'data' => $posts]);
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'categoria' => ['required', Rule::in(ForoPost::CATEGORIAS)],
            'titulo'    => ['required', 'string', 'min:3', 'max:80'],
            'contenido' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $post = new ForoPost($datos);
        $post->user_id = $request->user()->id; // el autor siempre es quien envía el token
        $post->save();
        $post->load('autor:id,name')->loadCount('comentarios');

        return response()->json([
            'success' => true,
            'message' => 'Publicación creada.',
            'data'    => $this->formatoPost($post, $request->user()->id),
        ], 201);
    }

    public function show(Request $request, ForoPost $post): JsonResponse
    {
        abort_if($post->oculto, 404);

        $uid = $request->user()->id;
        $post->load([
            'autor:id,name',
            'comentarios' => fn ($q) => $q->visibles()->with('autor:id,name')->oldest('id'),
        ]);

        $data = $this->formatoPost($post, $uid);
        $data['comentarios_count'] = $post->comentarios->count();
        $data['comentarios'] = $post->comentarios
            ->map(fn (ForoComentario $c) => $this->formatoComentario($c, $uid))
            ->values();

        return response()->json(['success' => true, 'message' => 'Publicación del foro.', 'data' => $data]);
    }

    public function destroy(Request $request, ForoPost $post): JsonResponse
    {
        // 404 (y no 403) para no revelar la existencia de contenido ajeno u oculto
        abort_unless($post->user_id === $request->user()->id, 404);
        $post->delete();

        return response()->json(['success' => true, 'message' => 'Publicación eliminada.', 'data' => null]);
    }

    public function comentar(Request $request, ForoPost $post): JsonResponse
    {
        abort_if($post->oculto, 404);

        $datos = $request->validate([
            'contenido' => ['required', 'string', 'min:2', 'max:500'],
        ]);

        $comentario = new ForoComentario($datos);
        $comentario->foro_post_id = $post->id;
        $comentario->user_id = $request->user()->id;
        $comentario->save();
        $comentario->load('autor:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Comentario publicado.',
            'data'    => $this->formatoComentario($comentario, $request->user()->id),
        ], 201);
    }

    public function eliminarComentario(Request $request, ForoComentario $comentario): JsonResponse
    {
        abort_unless($comentario->user_id === $request->user()->id, 404);
        $comentario->delete();

        return response()->json(['success' => true, 'message' => 'Comentario eliminado.', 'data' => null]);
    }

    /** Solo se expone el nombre del autor: nunca su correo ni otros datos. */
    private function formatoPost(ForoPost $p, int $uid): array
    {
        return [
            'id'                => $p->id,
            'categoria'         => $p->categoria,
            'titulo'            => $p->titulo,
            'contenido'         => $p->contenido,
            'autor'             => $p->autor?->name ?? 'Usuario',
            'es_mio'            => $p->user_id === $uid,
            'comentarios_count' => (int) ($p->comentarios_count ?? 0),
            'creado'            => $p->created_at?->toIso8601String(),
        ];
    }

    private function formatoComentario(ForoComentario $c, int $uid): array
    {
        return [
            'id'        => $c->id,
            'contenido' => $c->contenido,
            'autor'     => $c->autor?->name ?? 'Usuario',
            'es_mio'    => $c->user_id === $uid,
            'creado'    => $c->created_at?->toIso8601String(),
        ];
    }
}