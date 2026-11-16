<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ComunidadExport;
use App\Http\Controllers\Controller;
use App\Models\ForoPost;
use App\Services\ReporteComunidad;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

/** Reporte de comunidad: publicaciones del foro y consejos más consultados (solo admin). */
class ReporteComunidadController extends Controller
{
    public function index(Request $request, ReporteComunidad $servicio): View
    {
        $filtros = $this->filtros($request);

        return view('admin.reportes.comunidad', [
            'reporte'    => $servicio->generar($filtros),
            'filtros'    => $filtros,
            'categorias' => ForoPost::CATEGORIAS,
        ]);
    }

    public function descargar(Request $request, string $formato, ReporteComunidad $servicio)
    {
        $reporte = $servicio->generar($this->filtros($request));
        $archivo = 'reporte_comunidad_'.now()->format('Ymd_His');

        if ($formato === 'excel') {
            return Excel::download(new ComunidadExport($reporte), $archivo.'.xlsx');
        }

        return Pdf::loadView('admin.reportes.comunidad_pdf', [
            'reporte'  => $reporte,
            'periodo'  => ReporteComunidad::periodo($reporte),
            'generado' => now()->format('d/m/Y H:i'),
            'por'      => $request->user()->name,
        ])->setPaper('letter', 'portrait')->download($archivo.'.pdf');
    }

    /** Solo se aceptan los filtros conocidos y con valores válidos. */
    private function filtros(Request $request): array
    {
        $validos = $request->validate([
            'desde'     => ['nullable', 'date_format:Y-m-d'],
            'hasta'     => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'categoria' => ['nullable', Rule::in(ForoPost::CATEGORIAS)],
        ], [], ['desde' => 'fecha inicial', 'hasta' => 'fecha final', 'categoria' => 'categoría']);

        return array_filter($validos, fn ($v) => $v !== null && $v !== '');
    }
}