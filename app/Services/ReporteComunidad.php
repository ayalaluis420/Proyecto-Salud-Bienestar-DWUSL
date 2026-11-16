<?php

namespace App\Services;

use App\Models\Consejo;
use App\Models\ForoPost;

/**
 * Reporte de comunidad (Miembro 4): publicaciones del foro en un período
 * y consejos más consultados. Lo usan la vista previa, el PDF y el Excel.
 */
class ReporteComunidad
{
    public const LIMITE_CONSEJOS = 10;

    /** @param array{desde?: ?string, hasta?: ?string, categoria?: ?string} $filtros */
    public function generar(array $filtros): array
    {
        $desde = $filtros['desde'] ?? null;
        $hasta = $filtros['hasta'] ?? null;
        $categoria = $filtros['categoria'] ?? null;

        $posts = ForoPost::with('autor:id,name')
            ->withCount('comentarios')
            ->when($desde, fn ($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('created_at', '<=', $hasta))
            ->when($categoria, fn ($q) => $q->where('categoria', $categoria))
            ->latest('id')
            ->get();

        $consejos = Consejo::where('vistas', '>', 0)
            ->orderByDesc('vistas')->orderBy('id')
            ->take(self::LIMITE_CONSEJOS)
            ->get();

        return [
            'desde'     => $desde,
            'hasta'     => $hasta,
            'categoria' => $categoria,
            'resumen'   => [
                'publicaciones' => $posts->count(),
                'visibles'      => $posts->where('oculto', false)->count(),
                'ocultas'       => $posts->where('oculto', true)->count(),
                'comentarios'   => (int) $posts->sum('comentarios_count'),
            ],
            'posts' => $posts->map(fn (ForoPost $p) => [
                'id'          => $p->id,
                'titulo'      => $p->titulo,
                'autor'       => $p->autor?->name ?? '—',
                'categoria'   => $p->categoria,
                'comentarios' => (int) $p->comentarios_count,
                'estado'      => $p->oculto ? 'Oculta' : 'Visible',
                'fecha'       => $p->created_at?->format('d/m/Y H:i'),
            ])->all(),
            'consejos' => $consejos->values()->map(fn (Consejo $c, int $i) => [
                'posicion'  => $i + 1,
                'titulo'    => $c->titulo,
                'categoria' => $c->nombre_categoria,
                'estado'    => $c->publicado ? 'Publicado' : 'Oculto',
                'vistas'    => $c->vistas,
            ])->all(),
        ];
    }

    /** Texto del período para encabezados. */
    public static function periodo(array $reporte): string
    {
        $f = fn (?string $d) => $d ? date('d/m/Y', strtotime($d)) : null;

        return match (true) {
            $reporte['desde'] && $reporte['hasta'] => 'Del '.$f($reporte['desde']).' al '.$f($reporte['hasta']),
            (bool) $reporte['desde']               => 'Desde el '.$f($reporte['desde']),
            (bool) $reporte['hasta']               => 'Hasta el '.$f($reporte['hasta']),
            default                                => 'Todo el historial',
        };
    }
}