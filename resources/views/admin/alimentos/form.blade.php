@extends('layouts.app')
@section('title', $alimento->exists ? 'Editar alimento' : 'Nuevo alimento')
@section('content')
<div class="container"><div class="row justify-content-center"><div class="col-lg-8"><h1 class="h3 mb-3">{{ $alimento->exists ? 'Editar alimento' : 'Nuevo alimento' }}</h1>
<form class="card card-body" method="POST" action="{{ $alimento->exists ? route('alimentos.update',$alimento) : route('alimentos.store') }}">
@csrf @if ($alimento->exists) @method('PUT') @endif
@if ($errors->any())<div class="alert alert-danger">Revisa los campos señalados.</div>@endif
<div class="mb-3"><label for="nombre" class="form-label">Nombre</label><input id="nombre" name="nombre" value="{{ old('nombre',$alimento->nombre) }}" class="form-control @error('nombre') is-invalid @enderror" required maxlength="150">@error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label for="categoria" class="form-label">Categoría</label><select id="categoria" name="categoria" class="form-select @error('categoria') is-invalid @enderror" required><option value="">Selecciona una categoría</option>@foreach ($categorias as $c)<option value="{{ $c }}" @selected(old('categoria',$alimento->categoria) === $c)>{{ ucfirst($c) }}</option>@endforeach</select>@error('categoria')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="row">@foreach (['porcion_gramos'=>'Peso de la porción (g)','calorias'=>'Calorías por porción (kcal)'] as $campo=>$etiqueta)<div class="col-md-6 mb-3"><label for="{{ $campo }}" class="form-label">{{ $etiqueta }}</label><input id="{{ $campo }}" name="{{ $campo }}" type="number" step="0.01" min="{{ $campo === 'calorias' ? '0' : '0.01' }}" max="100000" value="{{ old($campo,$alimento->$campo) }}" class="form-control @error($campo) is-invalid @enderror" required>@error($campo)<div class="invalid-feedback">{{ $message }}</div>@enderror</div>@endforeach</div>
<div class="d-flex gap-2"><button class="btn btn-primary">Guardar alimento</button><a class="btn btn-outline-secondary" href="{{ route('alimentos.index') }}">Cancelar</a></div>
</form></div></div></div>
@endsection
