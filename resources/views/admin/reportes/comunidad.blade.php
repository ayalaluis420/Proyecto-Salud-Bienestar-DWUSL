@extends('layouts.app')

@section('title', 'Reporte de comunidad')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h3 mb-0">Reporte de comunidad</h1>
        <div class="d-flex flex-wrap gap-2">
            {{-- Las descargas respetan los filtros aplicados --}}
            <a href="{{ route('reportes.comunidad.descargar', ['formato' => 'pdf'] + $filtros) }}" class="btn btn-outline-danger">Exportar PDF</a>
            <a href="{{ route('reportes.comunidad.descargar', ['formato' => 'excel'] + $filtros) }}" class="btn btn-outline-success">Exportar Excel</a>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('reportes.comunidad') }}" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="desde" class="form-label mb-1">Desde</label>
                <input type="date" id="desde" name="desde" value="{{ old('desde', $filtros['desde'] ?? '') }}"
                       class="form-control @error('desde') is-invalid @enderror">
                @error('desde') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
                <label for="hasta" class="form-label mb-1">Hasta</label>
                <input type="date" id="hasta" name="hasta" value="{{ old('hasta', $filtros['hasta'] ?? '') }}"
                       class="form-control @error('hasta') is-invalid @enderror">
                @error('hasta') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label for="categoria" class="form-label mb-1">Categoría del foro</label>
                <select id="categoria" name="categoria" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($categorias as $c)
                        <option value="{{ $c }}" @selected(($filtros['categoria'] ?? '') === $c)>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary flex-fill">Filtrar</button>
                <a href="{{ route('reportes.comunidad') }}" class="btn btn-outline-secondary" title="Limpiar filtros">&times;</a>
            </div>
        </div>
    </form>

    {{-- Resumen --}}
    <div class="row g-3 mb-4">
        @foreach ([
            'Publicaciones' => $reporte['resumen']['publicaciones'],
            'Visibles'      => $reporte['resumen']['visibles'],
            'Ocultas'       => $reporte['resumen']['ocultas'],
            'Comentarios'   => $reporte['resumen']['comentarios'],
        ] as $etiqueta => $valor)
            <div class="col-6 col-lg-3">
                <div class="card tarjeta-resumen h-100">
                    <div class="card-body">
                        <div class="text-muted small">{{ $etiqueta }}</div>
                        <div class="valor">{{ $valor }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card mb-4">
        <div class="card-header">Publicaciones del foro — {{ \App\Services\ReporteComunidad::periodo($reporte) }}</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Categoría</th>
                        <th>Comentarios</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reporte['posts'] as $p)
                        <tr>
                            <td>{{ $p['id'] }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($p['titulo'], 50) }}</td>
                            <td>{{ $p['autor'] }}</td>
                            <td><span class="badge text-bg-secondary">{{ $p['categoria'] }}</span></td>
                            <td>{{ $p['comentarios'] }}</td>
                            <td>
                                <span class="badge {{ $p['estado'] === 'Oculta' ? 'text-bg-dark' : 'text-bg-success' }}">{{ $p['estado'] }}</span>
                            </td>
                            <td>{{ $p['fecha'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay publicaciones con esos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Consejos más consultados</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Posición</th>
                        <th>Título</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th class="text-end">Vistas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reporte['consejos'] as $c)
                        <tr>
                            <td>{{ $c['posicion'] }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($c['titulo'], 60) }}</td>
                            <td><span class="badge text-bg-secondary">{{ $c['categoria'] }}</span></td>
                            <td>{{ $c['estado'] }}</td>
                            <td class="text-end fw-bold">{{ $c['vistas'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Aún no hay consultas registradas desde la app.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer text-muted small">
            Las vistas son el total acumulado desde que se creó cada consejo; no dependen del período elegido.
        </div>
    </div>
</div>
@endsection