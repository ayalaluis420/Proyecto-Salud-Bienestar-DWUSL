{{--
    Formulario compartido por "crear" y "editar".
    Variables: $categorias, $consejo (opcional, solo al editar).
--}}
@php($editando = isset($consejo))

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>Revisa el formulario:</strong> hay {{ $errors->count() }} error(es) que corregir.
    </div>
@endif

<div class="row g-3">
    <div class="col-md-8">
        <label for="titulo" class="form-label">Título</label>
        <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $consejo->titulo ?? '') }}"
               class="form-control @error('titulo') is-invalid @enderror" required maxlength="150">
        @error('titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="categoria" class="form-label">Categoría</label>
        <select id="categoria" name="categoria" class="form-select @error('categoria') is-invalid @enderror" required>
            <option value="">Selecciona…</option>
            @foreach ($categorias as $valor => $nombre)
                <option value="{{ $valor }}" @selected(old('categoria', $consejo->categoria ?? '') === $valor)>{{ $nombre }}</option>
            @endforeach
        </select>
        @error('categoria') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label for="contenido" class="form-label">Contenido</label>
        <textarea id="contenido" name="contenido" rows="6" maxlength="2000"
                  class="form-control @error('contenido') is-invalid @enderror" required>{{ old('contenido', $consejo->contenido ?? '') }}</textarea>
        <div class="form-text">Entre 20 y 2000 caracteres.</div>
        @error('contenido') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        {{-- El campo oculto asegura que "desmarcado" llegue como 0 --}}
        <input type="hidden" name="publicado" value="0">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="publicado" name="publicado" value="1"
                   @checked(old('publicado', $editando ? $consejo->publicado : true))>
            <label class="form-check-label" for="publicado">Publicado (visible en la app)</label>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary">{{ $editando ? 'Guardar cambios' : 'Crear consejo' }}</button>
    <a href="{{ route('consejos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</div>