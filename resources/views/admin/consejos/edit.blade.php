@extends('layouts.app')

@section('title', 'Editar consejo')

@section('content')
<div class="container">
    <h1 class="h3 mb-3">Editar consejo</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('consejos.update', $consejo) }}" novalidate>
                @csrf
                @method('PUT')
                @include('admin.consejos._form')
            </form>
        </div>
    </div>
</div>
@endsection