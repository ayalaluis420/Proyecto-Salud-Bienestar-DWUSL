@extends('layouts.app')
@section('title','Objetivos de salud')
@section('content')
<div class="container">
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3">Objetivos de salud</h1>@if (auth()->user()->esAdmin())<a href="{{ route('planificacion.reportes', $filtros) }}" class="btn btn-outline-primary">Reportes</a>@endif</div>
<p class="text-muted">Consulta el avance registrado desde la aplicación móvil. Los objetivos se consideran cumplidos al alcanzar la meta.</p>
@include('admin.planificacion._filtros',['seccion'=>'objetivos'])
<div class="card table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Usuario</th><th>Objetivo</th><th>Periodo</th><th>Avance</th><th>Estado</th></tr></thead><tbody>
@forelse ($registros as $o)<tr><td>{{ $o->usuario->name }}<div class="small text-muted">{{ $o->usuario->email }}</div></td><td>{{ $o->titulo }}<div class="small text-muted">{{ $o->descripcion }}</div></td><td>{{ $o->fecha_inicio->format('d/m/Y') }} – {{ $o->fecha_fin->format('d/m/Y') }}</td><td>{{ $o->valor_actual }} / {{ $o->valor_meta }} {{ $o->unidad }}<div class="progress" role="progressbar" aria-label="Avance del objetivo" aria-valuenow="{{ $o->porcentaje }}" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" style="width: {{ $o->porcentaje }}%">{{ $o->porcentaje }}%</div></div></td><td><span class="badge {{ $o->estado === 'cumplido' ? 'text-bg-success' : ($o->estado === 'vencido' ? 'text-bg-danger' : 'text-bg-primary') }}">{{ str_replace('_',' ',ucfirst($o->estado)) }}</span></td></tr>
@empty<tr><td colspan="5" class="text-center text-muted py-4">No hay objetivos con los filtros seleccionados.</td></tr>@endforelse
</tbody></table></div><div class="mt-3">{{ $registros->links() }}</div><p class="small text-muted">{{ $registros->total() }} objetivo(s).</p>
</div>
@endsection
