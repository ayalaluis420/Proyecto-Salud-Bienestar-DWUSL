<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UsuariosExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/** Reporte de usuarios en PDF o Excel, filtrable por rol y estado (solo admin). */
class ReporteUsuarioController extends Controller
{
    public function pdf(Request $request): Response
    {
        $filtros = $this->filtros($request);

        $usuarios = User::with('perfil')->filtrar($filtros)->orderBy('id')->get();

        $pdf = Pdf::loadView('admin.reportes.usuarios_pdf', [
            'usuarios' => $usuarios,
            'filtros'  => $filtros,
            'roles'    => User::ROLES,
            'generado' => now()->format('d/m/Y H:i'),
            'por'      => $request->user()->name,
        ])->setPaper('letter', 'portrait');

        return $pdf->download('reporte_usuarios_'.now()->format('Ymd_His').'.pdf');
    }

    public function excel(Request $request): BinaryFileResponse
    {
        return Excel::download(
            new UsuariosExport($this->filtros($request)),
            'reporte_usuarios_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    /** Solo se aceptan los filtros conocidos y valores válidos. */
    private function filtros(Request $request): array
    {
        $validos = $request->validate([
            'rol'    => ['nullable', 'in:'.implode(',', array_keys(User::ROLES))],
            'estado' => ['nullable', 'in:activo,inactivo'],
            'buscar' => ['nullable', 'string', 'max:100'],
        ]);

        return array_filter($validos, fn ($v) => $v !== null && $v !== '');
    }
}
