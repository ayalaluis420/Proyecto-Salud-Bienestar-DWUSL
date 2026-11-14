
@extends('layouts.app')

@section('title', 'Editar ejercicio')

@section('content')
<div class="container">

    <div class="mb-3">
        <h1 class="h3">Editar tipo de ejercicio</h1>
        <p class="text-muted">
            Modifica la información de {{ $tipoEjercicio->nombre }}.
        </p>
    </div>

    <div class="card">
        <div class="card-body">

            <form method="POST"
                  action="{{ route('tiposEjercicio.update', ['tiposEjercicio' => $tipoEjercicio->id]) }}">

                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>

                    <input type="text"
                           id="nombre"
                           name="nombre"
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre', $tipoEjercicio->nombre) }}"
                           maxlength="100"
                           required>

                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>

                    <textarea id="descripcion"
                              name="descripcion"
                              class="form-control @error('descripcion') is-invalid @enderror"
                              rows="3">{{ old('descripcion', $tipoEjercicio->descripcion) }}</textarea>

                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="calorias_por_minuto" class="form-label">
                        Calorías por minuto
                    </label>

                    <input type="number"
                           id="calorias_por_minuto"
                           name="calorias_por_minuto"
                           class="form-control @error('calorias_por_minuto') is-invalid @enderror"
                           value="{{ old('calorias_por_minuto', $tipoEjercicio->calorias_por_minuto) }}"
                           min="0.01"
                           max="9999.99"
                           step="0.01"
                           required>

                    @error('calorias_por_minuto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox"
                           id="activo"
                           name="activo"
                           value="1"
                           class="form-check-input"
                           @checked(old('activo', $tipoEjercicio->activo))>

                    <label for="activo" class="form-check-label">
                        Ejercicio activo
                    </label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Actualizar
                    </button>

                    <a href="{{ route('tiposEjercicio.index') }}"
                       class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection
