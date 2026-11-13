@extends('layouts.app')

@section('title', 'Nuevo usuario')

@section('content')
<div class="container">
    <h1 class="h3 mb-3">Nuevo usuario</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('usuarios.store') }}" novalidate>
                @csrf
                @include('admin.usuarios._form')
            </form>
        </div>
    </div>
</div>
@endsection
