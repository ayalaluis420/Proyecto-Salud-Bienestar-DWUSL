<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Planificacion\FiltroRequest;
use App\Http\Requests\Planificacion\RecordatorioRequest;
use App\Models\Recordatorio;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RecordatorioController extends Controller
{
    public function index(FiltroRequest $r)
    {
        $q = $r->user()->recordatorios()->with('cumplimientos');
        if ($r->filled('tipo')) {
            $q->where('tipo', $r->validated('tipo'));
        }
        if ($r->filled('activo')) {
            $q->where('activo', $r->boolean('activo'));
        }

        return response()->json(['success' => true, 'message' => 'Recordatorios.', 'data' => $q->orderBy('hora')->paginate(20)]);
    }

    public function store(RecordatorioRequest $r)
    {
        return response()->json(['success' => true, 'message' => 'Recordatorio creado.', 'data' => $r->user()->recordatorios()->create($r->validated())->refresh()], 201);
    }

    public function show(Request $r, Recordatorio $recordatorio)
    {
        abort_unless($recordatorio->user_id === $r->user()->id, 404);

        return response()->json(['success' => true, 'message' => 'Recordatorio.', 'data' => $recordatorio->load('cumplimientos')]);
    }

    public function update(RecordatorioRequest $r, Recordatorio $recordatorio)
    {
        abort_unless($recordatorio->user_id === $r->user()->id, 404);
        // Evita cambiar retroactivamente el calendario de tomas ya registradas.
        if ($recordatorio->cumplimientos()->exists()) {
            foreach (['fecha_inicio', 'fecha_fin', 'frecuencia'] as $campo) {
                $anterior = in_array($campo, ['fecha_inicio', 'fecha_fin']) ? $recordatorio->$campo?->toDateString() : $recordatorio->$campo;
                if ($r->exists($campo) && $r->input($campo) !== $anterior) {
                    throw ValidationException::withMessages([$campo => 'Crea otro recordatorio para cambiar un calendario con tomas registradas.']);
                }
            }
        }
        $recordatorio->update($r->validated());

        return response()->json(['success' => true, 'message' => 'Recordatorio actualizado.', 'data' => $recordatorio->refresh()]);
    }

    public function destroy(Request $r, Recordatorio $recordatorio)
    {
        abort_unless($recordatorio->user_id === $r->user()->id, 404);
        $recordatorio->delete();

        return response()->json(['success' => true, 'message' => 'Recordatorio eliminado.', 'data' => null]);
    }

    public function cumplimiento(Request $r, Recordatorio $recordatorio)
    {
        abort_unless($recordatorio->user_id === $r->user()->id, 404);
        $datos = $r->validate(['fecha' => ['required', 'date_format:Y-m-d', 'before_or_equal:today']]);
        if (! $recordatorio->programadoEn($datos['fecha']) || (! $r->isMethod('DELETE') && ! $recordatorio->activo)) {
            throw ValidationException::withMessages(['fecha' => 'No hay una toma activa programada para esa fecha.']);
        }
        if ($r->isMethod('DELETE')) {
            $recordatorio->cumplimientos()->whereDate('fecha', $datos['fecha'])->delete();
        } else {
            $recordatorio->cumplimientos()->firstOrCreate($datos);
        }

        return response()->json(['success' => true, 'message' => 'Cumplimiento actualizado.', 'data' => $recordatorio->load('cumplimientos')]);
    }
}
