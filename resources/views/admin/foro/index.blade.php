@extends('layouts.app')

@section('title', 'Moderación del foro')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h3 mb-0">Moderación del foro</h1>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('foro.index') }}" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="buscar" class="form-label mb-1">Buscar</label>
                <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Título o contenido" value="{{ $filtros['buscar'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label for="categoria" class="form-label mb-1">Categoría</label>
                <select id="categoria" name="categoria" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($categorias as $c)
                        <option value="{{ $c }}" @selected(($filtros['categoria'] ?? '') === $c)>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="estado" class="form-label mb-1">Estado</label>
                <select id="estado" name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="visible" @selected(($filtros['estado'] ?? '') === 'visible')>Visibles</option>
                    <option value="oculto" @selected(($filtros['estado'] ?? '') === 'oculto')>Ocultas</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary flex-fill">Filtrar</button>
                <a href="{{ route('foro.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">&times;</a>
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
                        <th>Autor</th>
                        <th>Categoría</th>
                        <th>Comentarios</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($posts as $p)
                        <tr>
                            <td>{{ $p->id }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($p->titulo, 50) }}</td>
                            <td>{{ $p->autor?->name ?? '—' }}</td>
                            <td><span class="badge text-bg-secondary">{{ $p->categoria }}</span></td>
                            <td>{{ $p->comentarios_count }}</td>
                            <td>
                                @if ($p->oculto)
                                    <span class="badge text-bg-dark">Oculta</span>
                                @else
                                    <span class="badge text-bg-success">Visible</span>
                                @endif
                            </td>
                            <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('foro.show', $p) }}" class="btn btn-sm btn-outline-secondary">Ver</a>

                                    <form method="POST" action="{{ route('foro.visibilidad', $p) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $p->oculto ? 'btn-outline-success' : 'btn-outline-warning' }}">
                                            {{ $p->oculto ? 'Mostrar' : 'Ocultar' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('foro.destroy', $p) }}"
                                          data-confirm="¿Eliminar la publicación «{{ $p->titulo }}» y todos sus comentarios? Esta acción no se puede deshacer.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No hay publicaciones con esos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($posts->hasPages())
            <div class="card-footer">{{ $posts->links() }}</div>
        @endif
    </div>

    <p class="text-muted small mt-2">{{ $posts->total() }} publicación(es) con los filtros actuales.</p>
</div>
@endsection