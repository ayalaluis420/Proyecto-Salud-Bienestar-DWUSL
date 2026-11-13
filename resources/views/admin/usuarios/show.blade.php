@extends('layouts.app')

@section('title', 'Detalle de usuario')

@section('content')
@php($perfil = $usuario->perfil)
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">{{ $usuario->name }}</h1>
        <div class="d-flex gap-2">
            @if (Auth::user()->esAdmin())
                <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-primary">Editar</a>
            @endif
            <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Volver</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Cuenta</div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between"><span>Correo</span><strong>{{ $usuario->email }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>Rol</span><strong>{{ $usuario->nombre_rol }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Estado</span>
                        <span class="badge {{ $usuario->activo ? 'text-bg-success' : 'text-bg-dark' }}">{{ $usuario->activo ? 'Activo' : 'Inactivo' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between"><span>Registrado</span><strong>{{ $usuario->created_at?->format('d/m/Y H:i') }}</strong></li>
                </ul>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Perfil de salud</div>
                @if ($perfil)
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between"><span>Edad</span><strong>{{ $perfil->edad ? $perfil->edad.' años' : '—' }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Sexo</span><strong>{{ $perfil->sexo ? ucfirst($perfil->sexo) : '—' }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Altura</span><strong>{{ $perfil->altura ? $perfil->altura.' cm' : '—' }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>Peso</span><strong>{{ $perfil->peso ? $perfil->peso.' kg' : '—' }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span>IMC</span><strong>{{ $perfil->imc ?? '—' }}</strong></li>
                    </ul>
                @else
                    <div class="card-body text-muted">Este usuario aún no tiene perfil.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
