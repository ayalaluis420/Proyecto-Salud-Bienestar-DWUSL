<?php

namespace App\Exports;

use App\Services\ReporteComunidad;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/** Reporte de comunidad en Excel: una hoja para el foro y otra para los consejos. */
class ComunidadExport implements WithMultipleSheets
{
    public function __construct(private readonly array $reporte)
    {
    }

    public function sheets(): array
    {
        $r = $this->reporte;

        return [
            new ComunidadHoja(
                'Publicaciones del foro',
                [
                    ['Publicaciones del foro'],
                    ['Período', ReporteComunidad::periodo($r), 'Categoría', $r['categoria'] ?? 'Todas'],
                    ['Publicaciones', $r['resumen']['publicaciones'], 'Visibles', $r['resumen']['visibles'], 'Ocultas', $r['resumen']['ocultas'], 'Comentarios', $r['resumen']['comentarios']],
                    [],
                    ['ID', 'Título', 'Autor', 'Categoría', 'Comentarios', 'Estado', 'Fecha'],
                ],
                array_map('array_values', $r['posts']),
            ),
            new ComunidadHoja(
                'Consejos más consultados',
                [
                    ['Consejos más consultados'],
                    [],
                    ['Posición', 'Título', 'Categoría', 'Estado', 'Vistas'],
                ],
                array_map('array_values', $r['consejos']),
            ),
        ];
    }
}