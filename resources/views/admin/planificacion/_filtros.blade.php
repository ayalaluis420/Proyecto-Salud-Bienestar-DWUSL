@if ($errors->any())
<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<form method="GET" class="card card-body mb-3">
<div class="row g-3 align-items-end">
    <div class="col-md-4"><label for="user_id" class="form-label">Usuario</label><select name="user_id" id="user_id" class="form-select"><option value="">Todos los usuarios</option>@foreach ($usuarios as $u)<option value="{{ $u->id }}" @selected((string)($filtros['user_id'] ?? '') === (string)$u->id)>{{ $u->name }} · {{ $u->email }}</option>@endforeach</select></div>
    @if ($seccion === 'objetivos')
    <div class="col-md-2"><label for="estado" class="form-label">Estado</label><select name="estado" id="estado" class="form-select"><option value="">Todos</option>@foreach (['en_curso'=>'En curso','cumplido'=>'Cumplido','vencido'=>'Vencido'] as $valor=>$texto)<option value="{{ $valor }}" @selected(($filtros['estado'] ?? '') === $valor)>{{ $texto }}</option>@endforeach</select></div>
    @foreach (['desde'=>'Fecha límite desde','hasta'=>'Fecha límite hasta'] as $campo=>$texto)<div class="col-md-2"><label for="{{ $campo }}" class="form-label">{{ $texto }}</label><input type="date" name="{{ $campo }}" id="{{ $campo }}" value="{{ $filtros[$campo] ?? '' }}" class="form-control"></div>@endforeach
    @elseif ($seccion === 'recordatorios')
    <div class="col-md-2"><label for="tipo" class="form-label">Tipo</label><select name="tipo" id="tipo" class="form-select"><option value="">Todos</option>@foreach (['medicamento'=>'Medicamento','vitamina'=>'Vitamina'] as $valor=>$texto)<option value="{{ $valor }}" @selected(($filtros['tipo'] ?? '') === $valor)>{{ $texto }}</option>@endforeach</select></div>
    <div class="col-md-2"><label for="activo" class="form-label">Estado</label><select name="activo" id="activo" class="form-select"><option value="">Todos</option><option value="1" @selected((string)($filtros['activo'] ?? '') === '1')>Activo</option><option value="0" @selected((string)($filtros['activo'] ?? '') === '0')>Pausado</option></select></div>
    @else
    <div class="col-md-3"><label for="fecha" class="form-label">Fecha</label><input type="date" id="fecha" name="fecha" class="form-control" value="{{ $filtros['fecha'] ?? '' }}"></div>
    <div class="col-md-2"><label for="tipo" class="form-label">Comida</label><select name="tipo" id="tipo" class="form-select"><option value="">Todas</option>@foreach (['desayuno','almuerzo','cena','merienda'] as $tipo)<option @selected(($filtros['tipo'] ?? '') === $tipo) value="{{ $tipo }}">{{ ucfirst($tipo) }}</option>@endforeach</select></div>
    @endif
    <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary">Filtrar</button><a class="btn btn-outline-secondary" href="{{ url()->current() }}">Limpiar</a></div>
</div>
</form>
