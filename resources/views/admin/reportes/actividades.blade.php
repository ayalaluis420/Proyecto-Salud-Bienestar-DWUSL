<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de actividades físicas</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }

        h1, h2 {
            text-align: center;
        }

        h1 {
            font-size: 20px;
        }

        h2 {
            font-size: 15px;
            margin-top: 25px;
        }

        .filtros {
            margin: 15px 0;
            padding: 10px;
            background-color: #f2f2f2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: center;
        }

        th {
            background-color: #e8e8e8;
        }

        .sin-datos {
            text-align: center;
            padding: 12px;
        }
    </style>
</head>
<body>

    <h1>Salud y Bienestar</h1>
    <h2>Reporte de actividades físicas y entrenamientos</h2>

    <div class="filtros">
        <strong>Filtros aplicados:</strong><br>

        Usuario: {{ $request->usuario_id ?? 'Todos' }}<br>
        Fecha inicial: {{ $request->fecha_inicio ?? 'Sin especificar' }}<br>
        Fecha final: {{ $request->fecha_fin ?? 'Sin especificar' }}
    </div>

    <h2>Actividades físicas</h2>

    <table>
        <thead>
            <tr>
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
                    <td>{{ $actividad->usuario }}</td>
                    <td>{{ $actividad->fecha }}</td>
                    <td>{{ number_format($actividad->pasos) }}</td>
                    <td>{{ number_format($actividad->distancia_km, 2) }}</td>
                    <td>{{ number_format($actividad->calorias, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="sin-datos">
                        No hay actividades físicas registradas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Entrenamientos</h2>

    <table>
        <thead>
            <tr>
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
                    <td>{{ $entrenamiento->usuario }}</td>
                    <td>{{ $entrenamiento->fecha }}</td>
                    <td>{{ $entrenamiento->ejercicio }}</td>
                    <td>{{ $entrenamiento->duracion_min }}</td>
                    <td>{{ $entrenamiento->intensidad }}</td>
                    <td>{{ number_format($entrenamiento->calorias, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="sin-datos">
                        No hay entrenamientos registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>