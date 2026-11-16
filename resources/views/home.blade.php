@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php($esAdmin = Auth::user()->esAdmin())
<div class="container">
    <div class="mb-4">
        <h1 class="h3 mb-1">Dashboard</h1>
        <p class="text-muted mb-0">
            Hola, <strong>{{ Auth::user()->name }}</strong>. Has iniciado sesión como
            <strong>{{ Auth::user()->nombre_rol }}</strong>.
        </p>
    </div>

    {{-- Tarjetas de resumen --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card tarjeta-resumen h-100">
                <div class="card-body">
                    <div class="text-muted small">Usuarios de la app</div>
                    <div class="valor">{{ $resumen['usuarios'] }}</div>
                    <div class="small text-muted">{{ $resumen['usuarios_activos'] }} activos</div>
                    <a href="{{ route('usuarios.index') }}" class="small stretched-link">Ver usuarios</a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card tarjeta-resumen h-100">
                <div class="card-body">
                    <div class="text-muted small">Publicaciones del foro</div>
                    <div class="valor">{{ $resumen['posts'] }}</div>
                    <div class="small text-muted">{{ $resumen['posts_ocultos'] }} ocultas · {{ $resumen['comentarios'] }} comentarios</div>
                    <a href="{{ route('foro.index') }}" class="small stretched-link">Moderar el foro</a>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card tarjeta-resumen h-100">
                <div class="card-body">
                    <div class="text-muted small">Consejos publicados</div>
                    <div class="valor">{{ $resumen['consejos_public'] }}</div>
                    <div class="small text-muted">de {{ $resumen['consejos'] }} en total</div>
                    @if ($esAdmin)
                        <a href="{{ route('consejos.index') }}" class="small stretched-link">Gestionar consejos</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card tarjeta-resumen h-100">
                <div class="card-body">
                    <div class="text-muted small">Entrenamientos (7 días)</div>
                    <div class="valor">{{ $resumen['entrenamientos_7d'] }}</div>
                    <div class="small text-muted">{{ $resumen['recordatorios'] }} recordatorios activos</div>
                    @if ($esAdmin)
                        <a href="{{ route('actividadesEntrenamientos.index') }}" class="small stretched-link">Ver actividad</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">Actividad del foro en los últimos 7 días</div>
                <div class="card-body">
                    <div style="height: 260px;"><canvas id="graficoActividad" aria-label="Publicaciones y comentarios por día" role="img"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">Publicaciones por categoría</div>
                <div class="card-body">
                    <div style="height: 260px;"><canvas id="graficoCategorias" aria-label="Publicaciones del foro por categoría" role="img"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">Consejos más consultados</div>
                <div class="card-body">
                    @if ($masVistos->isEmpty())
                        <p class="text-muted mb-0">Aún no hay consultas registradas desde la app.</p>
                    @else
                        <div style="height: 240px;"><canvas id="graficoConsejos" aria-label="Consejos con más vistas" role="img"></canvas></div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">Objetivos de los usuarios</div>
                <ul class="list-group list-group-flush">
                    @foreach ($objetivos as $estado => $total)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $estado }}
                            <span class="fw-bold">{{ $total }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="card-body">
                    <a href="{{ route('objetivos.index') }}" class="small">Ver objetivos</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Últimas publicaciones --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Últimas publicaciones del foro</span>
            <a href="{{ route('foro.index') }}" class="small">Ver todas</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ultimosPosts as $p)
                        <tr>
                            <td><a href="{{ route('foro.show', $p) }}">{{ \Illuminate\Support\Str::limit($p->titulo, 50) }}</a></td>
                            <td>{{ $p->autor?->name ?? '—' }}</td>
                            <td><span class="badge text-bg-secondary">{{ $p->categoria }}</span></td>
                            <td>
                                @if ($p->oculto)
                                    <span class="badge text-bg-dark">Oculta</span>
                                @else
                                    <span class="badge text-bg-success">Visible</span>
                                @endif
                            </td>
                            <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Todavía no hay publicaciones en el foro.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

{{-- Gráficos con Chart.js (window.Chart lo expone resources/js/app.js) --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.Chart) return;

    const actividad = @json($actividad);
    const categorias = @json($categorias);
    const consejos = @json($masVistos->map(fn ($c) => ['titulo' => \Illuminate\Support\Str::limit($c->titulo, 26), 'vistas' => $c->vistas])->values());

    const AZUL = '#2a78d6';
    const NARANJA = '#eb6834';
    const REJILLA = '#e5e7eb';
    const TEXTO = '#52514e';

    window.Chart.defaults.font.family = "'Nunito', sans-serif";
    window.Chart.defaults.color = TEXTO;

    // Los conteos son enteros: el eje nunca muestra decimales.
    const ejeConteo = { beginAtZero: true, ticks: { precision: 0 }, grid: { color: REJILLA }, border: { display: false } };
    const ejeCategoria = { grid: { display: false }, border: { color: REJILLA } };

    new window.Chart(document.getElementById('graficoActividad'), {
        type: 'line',
        data: {
            labels: actividad.etiquetas,
            datasets: [
                { label: 'Publicaciones', data: actividad.posts, borderColor: AZUL, backgroundColor: AZUL, borderWidth: 2, pointRadius: 4, pointHoverRadius: 6, tension: 0.25 },
                { label: 'Comentarios', data: actividad.comentarios, borderColor: NARANJA, backgroundColor: NARANJA, borderWidth: 2, pointRadius: 4, pointHoverRadius: 6, pointStyle: 'rectRot', tension: 0.25 },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } },
            scales: { x: ejeCategoria, y: ejeConteo },
        },
    });

    new window.Chart(document.getElementById('graficoCategorias'), {
        type: 'bar',
        data: {
            labels: categorias.etiquetas,
            datasets: [{ label: 'Publicaciones', data: categorias.totales, backgroundColor: AZUL, borderRadius: 4, maxBarThickness: 28 }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: ejeConteo, y: ejeCategoria },
        },
    });

    const lienzoConsejos = document.getElementById('graficoConsejos');
    if (lienzoConsejos) {
        new window.Chart(lienzoConsejos, {
            type: 'bar',
            data: {
                labels: consejos.map((c) => c.titulo),
                datasets: [{ label: 'Vistas', data: consejos.map((c) => c.vistas), backgroundColor: AZUL, borderRadius: 4, maxBarThickness: 28 }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: ejeConteo, y: ejeCategoria },
            },
        });
    }
});
</script>
@endpush