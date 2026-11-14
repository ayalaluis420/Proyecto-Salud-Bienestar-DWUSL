
@extends('layouts.app')

@section('title', 'Actividades y entrenamientos')

@section('content')
<div class="container">

    {{-- Encabezado --}}
    <div class="mb-4">
        <h1 class="h3">Actividades y entrenamientos</h1>
        <p class="text-muted">
            Consulta la actividad física y los entrenamientos registrados por los usuarios.
        </p>

        <a href="{{ route('actividadesEntrenamientos.reporte.pdf', request()->query()) }}"
            class="btn btn-danger">
             <i class="fas fa-file-pdf"></i> Descargar reporte PDF
        </a>
    </div>

    {{-- Filtros generales --}}
    <div class="card mb-4">
        <div class="card-header">
            <strong>Filtros de búsqueda</strong>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('actividadesEntrenamientos.index') }}">

                <div class="row g-3">

                    <div class="col-md-3">
                        <label for="usuario_id" class="form-label">Usuario</label>

                        <select name="usuario_id" id="usuario_id" class="form-select">
                            <option value="">Todos los usuarios</option>

                            @foreach ($usuarios as $usuario)
                                <option value="{{ $usuario->id }}"
                                    @selected(request('usuario_id') == $usuario->id)>
                                    {{ $usuario->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="fecha_inicio" class="form-label">Desde</label>

                        <input type="date"
                               name="fecha_inicio"
                               id="fecha_inicio"
                               class="form-control"
                               value="{{ request('fecha_inicio') }}">
                    </div>

                    <div class="col-md-3">
                        <label for="fecha_fin" class="form-label">Hasta</label>

                        <input type="date"
                               name="fecha_fin"
                               id="fecha_fin"
                               class="form-control"
                               value="{{ request('fecha_fin') }}">
                    </div>

                    <div class="col-md-3">
                        <label for="tipo_ejercicio_id" class="form-label">
                            Tipo de ejercicio
                        </label>

                        <select name="tipo_ejercicio_id"
                                id="tipo_ejercicio_id"
                                class="form-select">

                            <option value="">Todos los ejercicios</option>

                            @foreach ($tiposEjercicio as $tipo)
                                <option value="{{ $tipo->id }}"
                                    @selected(request('tipo_ejercicio_id') == $tipo->id)>
                                    {{ $tipo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="intensidad" class="form-label">Intensidad</label>

                        <select name="intensidad"
                                id="intensidad"
                                class="form-select">

                            <option value="">Todas las intensidades</option>

                            <option value="Baja"
                                @selected(request('intensidad') == 'Baja')>
                                Baja
                            </option>

                            <option value="Media"
                                @selected(request('intensidad') == 'Media')>
                                Media
                            </option>

                            <option value="Alta"
                                @selected(request('intensidad') == 'Alta')>
                                Alta
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            Filtrar
                        </button>

                        <a href="{{ route('actividadesEntrenamientos.index') }}"
                           class="btn btn-outline-secondary">
                            Limpiar
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Actividades físicas --}}
    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Actividades físicas</h2>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                            <th>Pasos</th>
                            <th>Distancia (km)</th>
                            <th>Calorías</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($actividades as $actividad)
                            <tr>
                                <td>
                                    {{ $loop->iteration + ($actividades->currentPage() - 1) * $actividades->perPage() }}
                                </td>

                                <td>{{ $actividad->usuario }}</td>

                                <td>
                                    {{ \Carbon\Carbon::parse($actividad->fecha)->format('d/m/Y') }}
                                </td>

                                <td>{{ number_format($actividad->pasos) }}</td>

                                <td>{{ number_format($actividad->distancia_km, 2) }}</td>

                                <td>{{ number_format($actividad->calorias, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No se encontraron actividades físicas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            {{ $actividades->links() }}

        </div>
    </div>

    {{-- Entrenamientos --}}
    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Entrenamientos</h2>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                            <th>Ejercicio</th>
                            <th>Duración (min)</th>
                            <th>Intensidad</th>
                            <th>Calorías</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($entrenamientos as $entrenamiento)
                            <tr>
                                <td>
                                    {{ $loop->iteration + ($entrenamientos->currentPage() - 1) * $entrenamientos->perPage() }}
                                </td>

                                <td>{{ $entrenamiento->usuario }}</td>

                                <td>
                                    {{ \Carbon\Carbon::parse($entrenamiento->fecha)->format('d/m/Y H:i') }}
                                </td>

                                <td>{{ $entrenamiento->ejercicio }}</td>

                                <td>{{ $entrenamiento->duracion_min }}</td>

                                <td>{{ $entrenamiento->intensidad }}</td>

                                <td>{{ number_format($entrenamiento->calorias, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    No se encontraron entrenamientos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            {{ $entrenamientos->links() }}

        </div>
    </div>

    {{-- Estadísticas --}}
    <div class="mb-4">
        <h2 class="h5 mb-3">Estadísticas de actividad física</h2>

        <div class="row g-4">

            {{-- Pasos semanales --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <strong>Pasos de los últimos 7 días</strong>
                    </div>
                    <div class="card-body">
                        <canvas id="graficoPasosSemanal"></canvas>
                    </div>
                </div>
            </div>

            {{-- Calorías semanales --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <strong>Calorías de los últimos 7 días</strong>
                    </div>
                    <div class="card-body">
                        <canvas id="graficoCaloriasSemanal"></canvas>
                    </div>
                </div>
            </div>

            {{-- Pasos mensuales --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <strong>Pasos de los últimos 30 días</strong>
                    </div>
                    <div class="card-body">
                        <canvas id="graficoPasosMensual"></canvas>
                    </div>
                </div>
            </div>

            {{-- Calorías mensuales --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <strong>Calorías de los últimos 30 días</strong>
                    </div>
                    <div class="card-body">
                        <canvas id="graficoCaloriasMensual"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

{{-- Gráficos con Chart.js --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const datosSemanales = @json($estadisticasSemanales);
    const datosMensuales = @json($estadisticasMensuales);

    // Prepara las etiquetas y los valores de los gráficos
    function obtenerDatos(datos, campo) {
        return {
            etiquetas: datos.map(item => item.fecha),
            valores: datos.map(item => Number(item[campo]))
        };
    }

    // Crea un gráfico de barras
    function crearGrafico(elemento, datos, campo, etiqueta, color) {

        const informacion = obtenerDatos(datos, campo);
        const canvas = document.getElementById(elemento);

        new window.Chart(canvas, {
            type: 'bar',

            data: {
                labels: informacion.etiquetas,

                datasets: [{
                    label: etiqueta,
                    data: informacion.valores,
                    backgroundColor: color,
                    borderColor: color,
                    borderWidth: 1
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: true,

                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },

                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Gráficos semanales
    crearGrafico(
        'graficoPasosSemanal',
        datosSemanales,
        'total_pasos',
        'Pasos',
        'rgba(54, 162, 235, 0.7)'
    );

    crearGrafico(
        'graficoCaloriasSemanal',
        datosSemanales,
        'total_calorias',
        'Calorías',
        'rgba(255, 99, 132, 0.7)'
    );

    // Gráficos mensuales
    crearGrafico(
        'graficoPasosMensual',
        datosMensuales,
        'total_pasos',
        'Pasos',
        'rgba(54, 162, 235, 0.7)'
    );

    crearGrafico(
        'graficoCaloriasMensual',
        datosMensuales,
        'total_calorias',
        'Calorías',
        'rgba(255, 99, 132, 0.7)'
    );

});
</script>
@endpush


