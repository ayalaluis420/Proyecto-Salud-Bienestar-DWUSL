{{--
    Formulario compartido por "crear" y "editar".
    Variables: $roles, $usuario (opcional, solo al editar).
    Los errores de los Form Requests se muestran con is-invalid / invalid-feedback de Bootstrap.
--}}
@php
    $editando = isset($usuario);
    $perfil = $editando ? $usuario->perfil : null;
@endphp

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <strong>Revisa el formulario:</strong> hay {{ $errors->count() }} error(es) que corregir.
    </div>
@endif

<h2 class="h5">Datos de la cuenta</h2>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label for="name" class="form-label">Nombre completo</label>
        <input type="text" id="name" name="name" value="{{ old('name', $usuario->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror" required maxlength="255">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label">Correo electrónico</label>
        <input type="email" id="email" name="email" value="{{ old('email', $usuario->email ?? '') }}"
               class="form-control @error('email') is-invalid @enderror" required maxlength="255" autocomplete="off">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="password" class="form-label">
            Contraseña @if ($editando)<small class="text-muted">(déjala vacía para no cambiarla)</small>@endif
        </label>
        <input type="password" id="password" name="password"
               class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" @required(! $editando)>
        @error('password')
            {{-- se listan todos los requisitos que faltan, no solo el primero --}}
            <div class="invalid-feedback">
                @foreach ($errors->get('password') as $mensaje)
                    <div>{{ $mensaje }}</div>
                @endforeach
            </div>
        @else
            <div class="form-text">Mínimo 8 caracteres con mayúsculas, minúsculas, números y símbolos.</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               class="form-control" autocomplete="new-password" @required(! $editando)>
    </div>

    <div class="col-md-6">
        <label for="rol" class="form-label">Rol</label>
        <select id="rol" name="rol" class="form-select @error('rol') is-invalid @enderror" required>
            @foreach ($roles as $valor => $nombre)
                <option value="{{ $valor }}" @selected(old('rol', $usuario->rol ?? 'usuario') === $valor)>{{ $nombre }}</option>
            @endforeach
        </select>
        @error('rol') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check form-switch">
            <input type="hidden" name="activo" value="0">
            <input class="form-check-input @error('activo') is-invalid @enderror" type="checkbox" role="switch"
                   id="activo" name="activo" value="1" @checked(old('activo', $usuario->activo ?? true))>
            <label class="form-check-label" for="activo">Cuenta activa</label>
            @error('activo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<h2 class="h5">Perfil de salud <small class="text-muted">(opcional)</small></h2>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <label for="perfil_edad" class="form-label">Edad (años)</label>
        <input type="number" id="perfil_edad" name="perfil[edad]" min="10" max="100" step="1"
               value="{{ old('perfil.edad', $perfil?->edad) }}"
               class="form-control @error('perfil.edad') is-invalid @enderror">
        @error('perfil.edad') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="perfil_sexo" class="form-label">Sexo</label>
        <select id="perfil_sexo" name="perfil[sexo]" class="form-select @error('perfil.sexo') is-invalid @enderror">
            <option value="">— Sin especificar —</option>
            @foreach (['masculino' => 'Masculino', 'femenino' => 'Femenino', 'otro' => 'Otro'] as $valor => $texto)
                <option value="{{ $valor }}" @selected(old('perfil.sexo', $perfil?->sexo) === $valor)>{{ $texto }}</option>
            @endforeach
        </select>
        @error('perfil.sexo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="perfil_altura" class="form-label">Altura (cm)</label>
        <input type="number" id="perfil_altura" name="perfil[altura]" min="100" max="250" step="0.1"
               value="{{ old('perfil.altura', $perfil?->altura) }}"
               class="form-control @error('perfil.altura') is-invalid @enderror">
        @error('perfil.altura') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="perfil_peso" class="form-label">Peso (kg)</label>
        <input type="number" id="perfil_peso" name="perfil[peso]" min="20" max="300" step="0.1"
               value="{{ old('perfil.peso', $perfil?->peso) }}"
               class="form-control @error('perfil.peso') is-invalid @enderror">
        @error('perfil.peso') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $editando ? 'Guardar cambios' : 'Crear usuario' }}</button>
    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</div>
