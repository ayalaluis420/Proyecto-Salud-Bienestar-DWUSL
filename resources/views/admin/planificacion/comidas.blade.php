@extends('layouts.app')
@section('title','Planes de comida')
@section('content')
<div class="container"><h1 class="h3 mb-3">Planes de comida</h1>
@include('admin.planificacion._filtros',['seccion'=>'comidas'])
<div class="row g-3">
@forelse ($registros as $p)<div class="col-lg-6"><div class="card h-100"><div class="card-header d-flex justify-content-between"><strong>{{ $p->usuario->name }}</strong><span>{{ $p->fecha->format('d/m/Y') }} · {{ ucfirst($p->tipo) }}</span></div><div class="card-body"><ul class="list-group list-group-flush">@foreach ($p->detalles as $d)<li class="list-group-item d-flex justify-content-between"><span>{{ $d->alimento->nombre }} · {{ $d->porciones }} porción(es)</span><span>{{ round($d->calorias_porcion * $d->porciones,2) }} kcal</span></li>@endforeach</ul><p class="fw-bold text-end mt-3">Total: {{ $p->calorias_totales }} kcal</p>@if ($p->notas)<p class="text-muted mb-0">{{ $p->notas }}</p>@endif</div></div></div>
@empty<div class="col-12"><div class="card card-body text-center text-muted">No hay planes de comida con los filtros seleccionados.</div></div>@endforelse
</div><div class="mt-3">{{ $registros->links() }}</div></div>
@endsection
