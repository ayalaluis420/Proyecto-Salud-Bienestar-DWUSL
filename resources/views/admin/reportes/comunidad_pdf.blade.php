<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de comunidad</title>
    <style>
        @page { margin: 2.5cm 2cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        h2 { font-size: 12px; margin: 16px 0 6px; }
        .meta { color: #555; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2c7a5b; color: #fff; text-align: left; padding: 5px; }
        td { padding: 4px 5px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) td { background: #f5f8f6; }
        .resumen td { width: 25%; border: 1px solid #ddd; text-align: center; background: #fff; }
        .resumen strong { font-size: 14px; }
        .oculta { color: #b02a37; }
        .num { text-align: right; }
        .pie { margin-top: 12px; color: #555; }
    </style>
</head>
<body>
    <h1>Reporte de comunidad — {{ config('app.name') }}</h1>
    <div class="meta">
        Generado el {{ $generado }} por {{ $por }}<br>
        Período: <strong>{{ $periodo }}</strong> ·
        Categoría del foro: <strong>{{ $reporte['categoria'] ?? 'Todas' }}</strong>
    </div>

    <table class="resumen">
        <tr>
            <td><strong>{{ $reporte['resumen']['publicaciones'] }}</strong><br>Publicaciones</td>
            <td><strong>{{ $reporte['resumen']['visibles'] }}</strong><br>Visibles</td>
            <td><strong>{{ $reporte['resumen']['ocultas'] }}</strong><br>Ocultas</td>
            <td><strong>{{ $reporte['resumen']['comentarios'] }}</strong><br>Comentarios</td>
        </tr>
    </table>

    <h2>Publicaciones del foro</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Título</th>
                <th>Autor</th>
                <th>Categoría</th>
                <th>Coment.</th>
                <th>Estado</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reporte['posts'] as $p)
                <tr>
                    <td>{{ $p['id'] }}</td>
                    <td>{{ $p['titulo'] }}</td>
                    <td>{{ $p['autor'] }}</td>
                    <td>{{ $p['categoria'] }}</td>
                    <td>{{ $p['comentarios'] }}</td>
                    <td class="{{ $p['estado'] === 'Oculta' ? 'oculta' : '' }}">{{ $p['estado'] }}</td>
                    <td>{{ $p['fecha'] }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No hay publicaciones con los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Consejos más consultados</h2>
    <table>
        <thead>
            <tr>
                <th>Posición</th>
                <th>Título</th>
                <th>Categoría</th>
                <th>Estado</th>
                <th class="num">Vistas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reporte['consejos'] as $c)
                <tr>
                    <td>{{ $c['posicion'] }}</td>
                    <td>{{ $c['titulo'] }}</td>
                    <td>{{ $c['categoria'] }}</td>
                    <td>{{ $c['estado'] }}</td>
                    <td class="num">{{ $c['vistas'] }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Aún no hay consultas registradas desde la app.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pie">
        Las vistas de los consejos son el total acumulado y no dependen del período.
    </div>
</body>
</html>