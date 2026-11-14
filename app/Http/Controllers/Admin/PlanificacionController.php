<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Planificacion\FiltroRequest;
use App\Models\Objetivo;
use App\Models\PlanComida;
use App\Models\Recordatorio;
use App\Models\User;

class PlanificacionController extends Controller
{
    public function objetivos(FiltroRequest $r)
    {
        $q = Objetivo::with('usuario')->estado($r->validated('estado'));
        $this->filtrarUsuario($q, $r);
        if ($r->filled('desde')) {
            $q->whereDate('fecha_fin', '>=', $r->validated('desde'));
        }
        if ($r->filled('hasta')) {
            $q->whereDate('fecha_fin', '<=', $r->validated('hasta'));
        }

        return view('admin.planificacion.objetivos', $this->datos($r, $q->orderBy('fecha_fin')->paginate(15)->withQueryString()));
    }

    public function recordatorios(FiltroRequest $r)
    {
        $q = Recordatorio::with('usuario')->withCount('cumplimientos');
        $this->filtrarUsuario($q, $r);
        if ($r->filled('tipo')) {
            $q->where('tipo', $r->validated('tipo'));
        }
        if ($r->filled('activo')) {
            $q->where('activo', $r->boolean('activo'));
        }

        return view('admin.planificacion.recordatorios', $this->datos($r, $q->orderBy('hora')->paginate(15)->withQueryString()));
    }

    public function comidas(FiltroRequest $r)
    {
        $q = PlanComida::with(['usuario', 'detalles.alimento']);
        $this->filtrarUsuario($q, $r);
        if ($r->filled('fecha')) {
            $q->whereDate('fecha', $r->validated('fecha'));
        }
        if ($r->filled('tipo')) {
            $q->where('tipo', $r->validated('tipo'));
        }

        return view('admin.planificacion.comidas', $this->datos($r, $q->orderByDesc('fecha')->paginate(15)->withQueryString()));
    }

    private function filtrarUsuario($q, $r): void
    {
        if ($r->filled('user_id')) {
            $q->where('user_id', $r->validated('user_id'));
        }
    }

    private function datos($r, $registros): array
    {
        return ['registros' => $registros, 'filtros' => $r->validated(), 'usuarios' => User::orderBy('name')->get(['id', 'name', 'email'])];
    }
}
