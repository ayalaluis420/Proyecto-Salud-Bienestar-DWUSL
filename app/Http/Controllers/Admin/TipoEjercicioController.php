<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoEjercicio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TipoEjercicioController extends Controller
{
    public function index()
    {
        $tiposEjercicio = TipoEjercicio::orderBy('nombre')->paginate(10);

        return view('admin.tiposEjercicio.index', compact('tiposEjercicio'));
    }

    public function create()
    {
        return view('admin.tiposEjercicio.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:tipos_ejercicio,nombre'],
            'descripcion' => ['nullable', 'string'],
            'calorias_por_minuto' => ['required', 'numeric', 'min:0.01', 'max:9999.99'],
        ]);

        $datos['activo'] = $request->boolean('activo');

        TipoEjercicio::create($datos);

        return redirect()
            ->route('tiposEjercicio.index')
            ->with('success', 'Tipo de ejercicio registrado correctamente.');
    }

    public function edit(TipoEjercicio $tiposEjercicio)
    {
        return view('admin.tiposEjercicio.edit', [
            'tipoEjercicio' => $tiposEjercicio
        ]);
    }

    public function update(Request $request, TipoEjercicio $tiposEjercicio)
    {
        $datos = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tipos_ejercicio', 'nombre')
                    ->ignore($tiposEjercicio->id),
            ],
            'descripcion' => ['nullable', 'string'],
            'calorias_por_minuto' => ['required', 'numeric', 'min:0.01', 'max:9999.99'],
        ]);

        $datos['activo'] = $request->boolean('activo');

        $tiposEjercicio->update($datos);

        return redirect()
            ->route('tiposEjercicio.index')
            ->with('success', 'Tipo de ejercicio actualizado correctamente.');
    }

    public function destroy(TipoEjercicio $tiposEjercicio)
    {
        try {
            $tiposEjercicio->delete();

            return redirect()
                ->route('tiposEjercicio.index')
                ->with('success', 'Tipo de ejercicio eliminado correctamente.');

        } catch (\Illuminate\Database\QueryException $e) {

            return redirect()
                ->route('tiposEjercicio.index')
                ->with('error', 'No se puede eliminar este ejercicio porque está asociado a entrenamientos.');
        }
    }
}
