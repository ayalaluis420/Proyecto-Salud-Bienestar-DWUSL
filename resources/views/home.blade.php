@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Panel de Salud y Bienestar</div>

                <div class="card-body">
                    <p class="mb-1">Hola, <strong>{{ Auth::user()->name }}</strong>.</p>
                    <p class="text-muted">Has iniciado sesión como <strong>{{ Auth::user()->nombre_rol }}</strong>.</p>

                    <a href="{{ route('usuarios.index') }}" class="btn btn-primary">Ver usuarios</a>
                    {{-- Miembro 4: aquí irá el dashboard con tarjetas y gráficos --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
