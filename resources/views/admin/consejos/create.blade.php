@extends('layouts.app')

@section('title', 'Nuevo consejo')

@section('content')
<div class="container">
    <h1 class="h3 mb-3">Nuevo consejo</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('consejos.store') }}" novalidate>
                @csrf
                @include('admin.consejos._form')
            </form>
        </div>
    </div>
</div>
@endsection