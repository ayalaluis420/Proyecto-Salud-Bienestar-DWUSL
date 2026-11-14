@extends('layouts.app')
@section('title','Recordatorios')
@section('content')
<div class="container">
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3">Recordatorios</h1>@if (auth()->user()->esAdmin())<a href="{{ route('planificacion.reportes', $filtros) }}" class="btn btn-outline-primary">Reportes</a>@endif</div>
<p class="text-muted">Medicamentos y vitaminas configurados por cada usuario. Las horas corresponden a la configuración del usuario.</p>
@include('admin.planificacion._filtros',['seccion'=>'recordatorios'])
<div class="card table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Usuario</th><th>Recordatorio</th><th>Dosis</th><th>Hora / frecuencia</th><th>Vigencia</th><th>Estado</th><th>Tomas registradas</th></tr></thead><tbody>
@forelse ($registros as $r)<tr><td>{{ $r->usuario->name }}</td><td>{{ $r->nombre }}<div class="small text-muted">{{ ucfirst($r->tipo) }}</div></td><td>{{ $r->dosis }}</td><td>{{ substr($r->hora,0,5) }} · {{ $r->frecuencia }}</td><td>{{ $r->fecha_inicio->format('d/m/Y') }} – {{ $r->fecha_fin?->format('d/m/Y') ?? 'Sin fecha final' }}</td><td><span class="badge {{ $r->activo ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $r->activo ? 'Activo' : 'Pausado' }}</span></td><td>{{ $r->cumplimientos_count }}</td></tr>
@empty<tr><td colspan="7" class="text-center text-muted py-4">No hay recordatorios con los filtros seleccionados.</td></tr>@endforelse
</tbody></table></div><div class="mt-3">{{ $registros->links() }}</div>
</div>
@endsection
