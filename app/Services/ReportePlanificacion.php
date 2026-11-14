<?php

namespace App\Services;

use App\Models\Objetivo;
use App\Models\Recordatorio;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Validation\ValidationException;

class ReportePlanificacion
{
    public function generar(string $tipo, array $filtros): array
    {
        $desde = Carbon::parse($filtros['desde'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $hasta = Carbon::parse($filtros['hasta'] ?? today()->toDateString())->startOfDay();
        if ($hasta->lt($desde) || $desde->diffInDays($hasta) > 365) {
            throw ValidationException::withMessages(['hasta' => 'El periodo debe ser de hasta 366 días y terminar después del inicio.']);
        }
        $q = $tipo === 'objetivos' ? Objetivo::with('usuario')->estado($filtros['estado'] ?? null)->whereBetween('fecha_fin', [$desde->toDateString(), $hasta->toDateString()]) : Recordatorio::with(['usuario', 'cumplimientos' => fn ($q) => $q->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])])->whereDate('fecha_inicio', '<=', $hasta)->where(fn ($q) => $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $desde));
        if (! empty($filtros['user_id'])) {
            $q->where('user_id', $filtros['user_id']);
        }
        if ($tipo === 'recordatorios') {
            if (! empty($filtros['tipo'])) {
                $q->where('tipo', $filtros['tipo']);
            }
            if (isset($filtros['activo'])) {
                $q->where('activo', (bool) $filtros['activo']);
            }
        }
        $filas = [];
        $cumplidos = 0;
        $esperados = 0;
        foreach ($q->orderBy('user_id')->orderBy('id')->get() as $item) {
            if ($tipo === 'objetivos') {
                $cumplidos += $item->estado === 'cumplido' ? 1 : 0;
                $esperados++;
                $filas[] = [$item->usuario->name, $item->usuario->email, $item->titulo, $item->fecha_fin->format('d/m/Y'), $item->valor_actual.' / '.$item->valor_meta.' '.$item->unidad, $item->estado, $item->porcentaje.'%'];
            } else {
                $programados = 0;
                // Solo tomas que ya debían ocurrir; nunca se cuenta el futuro como incumplido.
                if ($desde->lte(today())) {
                    foreach (CarbonPeriod::create($desde, $hasta->copy()->min(today())) as $dia) {
                        if ($item->programadoEn($dia)) {
                            $programados++;
                        }
                    }
                }
                $hechos = $item->cumplimientos->filter(fn ($c) => $item->programadoEn($c->fecha))->count();
                $cumplidos += $hechos;
                $esperados += $programados;
                $filas[] = [$item->usuario->name, $item->usuario->email, $item->nombre, $item->dosis, substr($item->hora, 0, 5), $item->frecuencia, $item->activo ? 'Activo' : 'Pausado', $hechos, $programados, $programados ? round($hechos / $programados * 100, 1).'%' : 'Sin tomas'];
            }
        }

        return [
            'tipo' => $tipo, 'desde' => $desde->format('d/m/Y'), 'hasta' => $hasta->format('d/m/Y'),
            'encabezados' => $tipo === 'objetivos' ? ['Usuario', 'Correo', 'Objetivo', 'Fecha límite', 'Avance', 'Estado', 'Progreso'] : ['Usuario', 'Correo', 'Recordatorio', 'Dosis', 'Hora', 'Frecuencia', 'Estado actual', 'Tomas registradas', 'Tomas previstas', 'Cumplimiento'],
            'filas' => $filas, 'cumplidos' => $cumplidos, 'esperados' => $esperados,
            'porcentaje' => $esperados ? round($cumplidos / $esperados * 100, 1) : 0,
        ];
    }
}
