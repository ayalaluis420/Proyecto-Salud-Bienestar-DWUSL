@extends('layouts.app')

@section('title', 'Tipos de ejercicios')

@section('content')
<div class="container">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h3 mb-0">Tipos de ejercicios</h1>

        <a href="{{ route('tiposEjercicio.create') }}" class="btn btn-primary">
            Nuevo ejercicio
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Calorías por minuto</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($tiposEjercicio as $tipo)
                        <tr>
                            <td>{{ $loop->iteration + ($tiposEjercicio->currentPage() - 1) * $tiposEjercicio->perPage() }}</td>
                            <td>{{ $tipo->nombre }}</td>
                            <td>{{ $tipo->descripcion ?? '—' }}</td>
                            <td>{{ number_format($tipo->calorias_por_minuto, 2) }}</td>

                            <td>
                                @if ($tipo->activo)
                                    <span class="badge text-bg-success">Activo</span>
                                @else
                                    <span class="badge text-bg-dark">Inactivo</span>
                                @endif
                            </td>

                            <td class="text-end">
                                <div class="d-inline-flex gap-1">

                                    <a href="{{ route('tiposEjercicio.edit', $tipo) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        Editar
                                    </a>

                                    <form method="POST"
                                          action="{{ route('tiposEjercicio.destroy', $tipo) }}"
                                          class="form-eliminar d-inline">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-danger">
                                            Eliminar
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No hay tipos de ejercicios registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tiposEjercicio->hasPages())
            <div class="card-footer">
                {{ $tiposEjercicio->links() }}
            </div>
        @endif
    </div>

    <p class="text-muted small mt-2">
        {{ $tiposEjercicio->total() }} tipo(s) de ejercicio registrado(s).
    </p>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.form-eliminar').forEach(form => {

        form.addEventListener('submit', function (e) {

            e.preventDefault();

            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción eliminará el tipo de ejercicio seleccionado.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {

                if (result.isConfirmed) {
                    form.submit();
                }

            });

        });

    });

});
</script>

@endsection
