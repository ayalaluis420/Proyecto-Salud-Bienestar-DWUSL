<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de usuarios</title>
    <style>
        @page { margin: 2.5cm 2cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .meta { color: #555; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2c7a5b; color: #fff; text-align: left; padding: 5px; }
        td { padding: 4px 5px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) td { background: #f5f8f6; }
        .inactivo { color: #b02a37; }
        .pie { margin-top: 12px; color: #555; }
    </style>
</head>
<body>
    <h1>Reporte de usuarios — {{ config('app.name') }}</h1>
    <div class="meta">
        Generado el {{ $generado }} por {{ $por }}<br>
        Filtros:
        Rol: <strong>{{ isset($filtros['rol']) ? ($roles[$filtros['rol']] ?? $filtros['rol']) : 'Todos' }}</strong> ·
        Estado: <strong>{{ isset($filtros['estado']) ? ucfirst($filtros['estado']) : 'Todos' }}</strong>
        @if (! empty($filtros['buscar'])) · Búsqueda: <strong>{{ $filtros['buscar'] }}</strong> @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Edad</th>
                <th>Sexo</th>
                <th>Registro</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($usuarios as $u)
                <tr>
                    <td>{{ $u->id }}</td>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>{{ $u->nombre_rol }}</td>
                    <td class="{{ $u->activo ? '' : 'inactivo' }}">{{ $u->activo ? 'Activo' : 'Inactivo' }}</td>
                    <td>{{ $u->perfil?->edad ?? '—' }}</td>
                    <td>{{ $u->perfil?->sexo ? ucfirst($u->perfil->sexo) : '—' }}</td>
                    <td>{{ $u->created_at?->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="8">No hay usuarios con los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pie">Total: {{ $usuarios->count() }} usuario(s).</div>
</body>
</html>
