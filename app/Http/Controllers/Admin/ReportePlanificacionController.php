<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PlanificacionExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Planificacion\FiltroRequest;
use App\Models\User;
use App\Services\ReportePlanificacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportePlanificacionController extends Controller
{
    public function index(FiltroRequest $r)
    {
        return view('admin.planificacion.reportes', ['usuarios' => User::orderBy('name')->get(['id', 'name', 'email']), 'filtros' => $r->validated()]);
    }

    public function descargar(FiltroRequest $r, string $tipo, string $formato, ReportePlanificacion $servicio)
    {
        $reporte = $servicio->generar($tipo, $r->validated());
        $archivo = 'cumplimiento_'.$tipo.'_'.now()->format('Ymd_His');
        if ($formato === 'excel') {
            return Excel::download(new PlanificacionExport($reporte), $archivo.'.xlsx');
        }

        return Pdf::loadView('admin.reportes.planificacion_pdf', ['reporte' => $reporte, 'por' => $r->user()->name])->setPaper('letter', 'landscape')->download($archivo.'.pdf');
    }
}
