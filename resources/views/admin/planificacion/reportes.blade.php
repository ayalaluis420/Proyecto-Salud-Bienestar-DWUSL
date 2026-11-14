@extends('layouts.app')
@section('title','Reportes de planificación')
@section('content')
<div class="container"><h1 class="h3 mb-3">Reportes de cumplimiento</h1>
<p class="text-muted">Selecciona un periodo de hasta 366 días. Los objetivos se filtran por su fecha límite. En recordatorios se comparan las tomas registradas con las previstas hasta hoy, incluidas las de recordatorios pausados; no se descuentan pausas sin historial.</p>
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
@foreach (['objetivos'=>'Objetivos de salud','recordatorios'=>'Medicamentos y vitaminas'] as $tipo=>$titulo)
<form method="GET" class="card card-body mb-3"><h2 class="h5">{{ $titulo }}</h2><div class="row g-3 align-items-end">
<div class="col-md-4"><label for="user_{{ $tipo }}" class="form-label">Usuario</label><select name="user_id" id="user_{{ $tipo }}" class="form-select"><option value="">Todos</option>@foreach ($usuarios as $u)<option value="{{ $u->id }}" @selected((string)old('user_id',$filtros['user_id'] ?? '') === (string)$u->id)>{{ $u->name }} · {{ $u->email }}</option>@endforeach</select></div>
<div class="col-md-3"><label for="desde_{{ $tipo }}" class="form-label">Desde</label><input type="date" class="form-control" required name="desde" id="desde_{{ $tipo }}" value="{{ old('desde',$filtros['desde'] ?? now()->startOfMonth()->toDateString()) }}"></div>
<div class="col-md-3"><label for="hasta_{{ $tipo }}" class="form-label">Hasta</label><input type="date" class="form-control" required name="hasta" id="hasta_{{ $tipo }}" value="{{ old('hasta',$filtros['hasta'] ?? today()->toDateString()) }}"></div>
@if ($tipo === 'objetivos')<div class="col-md-3"><label for="estado" class="form-label">Estado</label><select name="estado" id="estado" class="form-select"><option value="">Todos</option>@foreach (['en_curso','cumplido','vencido'] as $estado)<option value="{{ $estado }}" @selected(($filtros['estado'] ?? '') === $estado)>{{ ucfirst(str_replace('_',' ',$estado)) }}</option>@endforeach</select></div>@endif
<div class="col-md-5 d-flex gap-2"><button formaction="{{ route('planificacion.descargar',[$tipo,'pdf']) }}" class="btn btn-outline-danger">Descargar PDF</button><button formaction="{{ route('planificacion.descargar',[$tipo,'excel']) }}" class="btn btn-outline-success">Descargar Excel</button></div>
</div></form>@endforeach
</div>
@endsection
