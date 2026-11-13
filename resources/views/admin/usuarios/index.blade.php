@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
@php($esAdmin = Auth::user()->esAdmin())
<div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h3 mb-0">Usuarios</h1>

        @if ($esAdmin)
            <div class="d-flex flex-wrap gap-2">
                {{-- El reporte respeta los filtros que estén aplicados --}}
                <a href="{{ route('usuarios.reporte.pdf', $filtros) }}" class="btn btn-outline-danger">Exportar PDF</a>
                <a href="{{ route('usuarios.reporte.excel', $filtros) }}" class="btn btn-outline-success">Exportar Excel</a>
                <a href="{{ route('usuarios.create') }}" class="btn btn-primary">Nuevo usuario</a>
            </div>
        @endif
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('usuarios.index') }}" class="card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="buscar" class="form-label mb-1">Buscar</label>
                <input type="text" id="buscar" name="buscar" class="form-control" placeholder="Nombre o correo" value="{{ $filtros['buscar'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label for="rol" class="form-label mb-1">Rol</label>
                <select id="rol" name="rol" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($roles as $valor => $nombre)
                        <option value="{{ $valor }}" @selected(($filtros['rol'] ?? '') === $valor)>{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="estado" class="form-label mb-1">Estado</label>
                <select id="estado" name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="activo" @selected(($filtros['estado'] ?? '') === 'activo')>Activos</option>
                    <option value="inactivo" @selected(($filtros['estado'] ?? '') === 'inactivo')>Inactivos</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-secondary flex-fill">Filtrar</button>
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">&times;</a>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Edad</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usuarios as $u)
                        <tr>
                            <td>{{ $u->id }}</td>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>
                                <span class="badge {{ match($u->rol) { 'admin' => 'text-bg-danger', 'moderador' => 'text-bg-warning', default => 'text-bg-secondary' } }}">
                                    {{ $u->nombre_rol }}
                                </span>
                            </td>
                            <td>
                                @if ($u->activo)
                                    <span class="badge text-bg-success">Activo</span>
                                @else
                                    <span class="badge text-bg-dark">Inactivo</span>
                                @endif
                            </td>
                            <td>{{ $u->perfil?->edad ?? '—' }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('usuarios.show', $u) }}" class="btn btn-sm btn-outline-secondary">Ver</a>

                                    @if ($esAdmin)
                                        <a href="{{ route('usuarios.edit', $u) }}" class="btn btn-sm btn-outline-primary">Editar</a>

                                        @unless ($u->is(Auth::user()))
                                            <form method="POST" action="{{ route('usuarios.toggle', $u) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm {{ $u->activo ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                                    {{ $u->activo ? 'Desactivar' : 'Activar' }}
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('usuarios.destroy', $u) }}"
                                                  onsubmit="return confirm('¿Eliminar a {{ e($u->name) }}? Esta acción no se puede deshacer.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                            </form>
                                        @endunless
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No se encontraron usuarios con esos filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($usuarios->hasPages())
            <div class="card-footer">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>

    <p class="text-muted small mt-2">{{ $usuarios->total() }} usuario(s) en total con los filtros actuales.</p>
</div>
@endsection
