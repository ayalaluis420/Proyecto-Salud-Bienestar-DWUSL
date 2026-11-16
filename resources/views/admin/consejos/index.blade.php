@extends('layouts.app')

@section('title', 'Consejos')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h3 mb-0">Consejos de salud</h1>
        <a href="{{ route('consejos.create') }}" class="btn btn-primary">Nuevo consejo</a>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('consejos.index') }}" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="buscar" class="form-label mb-1">Buscar</label>
                <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Título o contenido" value="{{ $filtros['buscar'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label for="categoria" class="form-label mb-1">Categoría</label>
                <select id="categoria" name="categoria" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($categorias as $valor => $nombre)
                        <option value="{{ $valor }}" @selected(($filtros['categoria'] ?? '') === $valor)>{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="estado" class="form-label mb-1">Estado</label>
                <select id="estado" name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="publicado" @selected(($filtros['estado'] ?? '') === 'publicado')>Publicados</option>
                    <option value="oculto" @selected(($filtros['estado'] ?? '') === 'oculto')>Ocultos</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary flex-fill">Filtrar</button>
                <a href="{{ route('consejos.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">&times;</a>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Título</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th>Vistas</th>
                        <th>Fecha</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($consejos as $c)
                        <tr>
                            <td>{{ $c->id }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($c->titulo, 60) }}</td>
                            <td><span class="badge text-bg-secondary">{{ $c->nombre_categoria }}</span></td>
                            <td>
                                @if ($c->publicado)
                                    <span class="badge text-bg-success">Publicado</span>
                                @else
                                    <span class="badge text-bg-dark">Oculto</span>
                                @endif
                            </td>
                            <td>{{ $c->vistas }}</td>
                            <td>{{ $c->created_at->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('consejos.edit', $c) }}" class="btn btn-sm btn-outline-primary">Editar</a>

                                    <form method="POST" action="{{ route('consejos.toggle', $c) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $c->publicado ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                            {{ $c->publicado ? 'Ocultar' : 'Publicar' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('consejos.destroy', $c) }}"
                                          data-confirm="¿Eliminar el consejo «{{ $c->titulo }}»? Esta acción no se puede deshacer.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay consejos con esos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($consejos->hasPages())
            <div class="card-footer">{{ $consejos->links() }}</div>
        @endif
    </div>

    <p class="text-muted small mt-2">{{ $consejos->total() }} consejo(s) con los filtros actuales.</p>
</div>
@endsection