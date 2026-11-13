@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
<div class="container">
    <h1 class="h3 mb-3">Editar usuario: {{ $usuario->name }}</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('usuarios.update', $usuario) }}" novalidate>
                @csrf
                @method('PUT')
                @include('admin.usuarios._form')
            </form>
        </div>
    </div>
</div>
@endsection
