<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Salud y Bienestar'))</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="{{ auth()->check() ? 'panel-con-menu' : 'panel-invitado' }}">
    {{--
        Layout general del panel (Miembro 4).
        Contrato con los demás módulos: sus vistas solo usan @extends('layouts.app'),
        @section('title') y @section('content'). Para scripts propios: @push('scripts').
        Los enlaces del menú se muestran según el rol (esPersonal / esAdmin) y los
        módulos aún no registrados se omiten con Route::has().
    --}}
    <div id="app">
        <nav class="navbar navbar-expand navbar-dark panel-navbar shadow-sm">
            <div class="container-fluid">
                @auth
                    @if (Auth::user()->esPersonal())
                        <button class="btn btn-link text-white d-lg-none me-2 px-1"
                                type="button"
                                data-bs-toggle="offcanvas"
                                data-bs-target="#menuLateral"
                                aria-controls="menuLateral"
                                aria-label="Abrir menú">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                    @endif
                @endauth

                <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                    {{ config('app.name', 'Salud y Bienestar') }}
                </a>

                <ul class="navbar-nav ms-auto">
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown"
                               class="nav-link dropdown-toggle"
                               href="#"
                               role="button"
                               data-bs-toggle="dropdown"
                               aria-haspopup="true"
                               aria-expanded="false"
                               v-pre>
                                <span class="d-none d-sm-inline">{{ Auth::user()->name }}</span>
                                <span class="badge text-bg-light">{{ Auth::user()->nombre_rol }}</span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item"
                                   href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                   document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </nav>

        <div class="panel-cuerpo">
            @auth
                @if (Auth::user()->esPersonal())
                    @php
                        $esAdmin = Auth::user()->esAdmin();
                        // [ruta, patrón para "activo", texto, solo admin?]
                        $grupos = [
                            'General' => [
                                ['home', 'home', 'Dashboard', false],
                            ],
                            'Usuarios' => [
                                ['usuarios.index', 'usuarios.*', 'Usuarios', false],
                            ],
                            'Actividad' => [
                                ['tiposEjercicio.index', 'tiposEjercicio.*', 'Tipos de ejercicios', true],
                                ['actividadesEntrenamientos.index', 'actividadesEntrenamientos.*', 'Actividades y entrenamientos', true],
                            ],
                            'Planificación' => [
                                ['objetivos.index', 'objetivos.*', 'Objetivos', false],
                                ['recordatorios.index', 'recordatorios.*', 'Recordatorios', false],
                                ['planes-comida.index', 'planes-comida.*', 'Planes de comida', false],
                                ['alimentos.index', 'alimentos.*', 'Alimentos', false],
                                ['planificacion.reportes', 'planificacion.*', 'Reportes de planificación', true],
                            ],
                            'Comunidad' => [
                                ['foro.index', 'foro.*', 'Moderación del foro', false],
                                ['consejos.index', 'consejos.*', 'Consejos', true],
                                ['reportes.comunidad', 'reportes.comunidad*', 'Reporte de comunidad', true],
                            ],
                        ];
                    @endphp

                    <aside id="menuLateral"
                           class="offcanvas-lg offcanvas-start panel-sidebar"
                           tabindex="-1"
                           aria-labelledby="menuLateralTitulo">
                        <div class="offcanvas-header d-lg-none">
                            <h5 class="offcanvas-title" id="menuLateralTitulo">Menú</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#menuLateral" aria-label="Cerrar"></button>
                        </div>
                        <div class="offcanvas-body d-block p-0">
                            <nav class="nav flex-column py-3">
                                @foreach ($grupos as $titulo => $enlaces)
                                    @php
                                        $visibles = collect($enlaces)->filter(
                                            fn ($e) => Route::has($e[0]) && (! $e[3] || $esAdmin)
                                        );
                                    @endphp
                                    @if ($visibles->isNotEmpty())
                                        <div class="panel-sidebar-titulo">{{ $titulo }}</div>
                                        @foreach ($visibles as $e)
                                            <a class="nav-link {{ request()->routeIs($e[1]) ? 'active' : '' }}"
                                               href="{{ route($e[0]) }}">
                                                {{ $e[2] }}
                                            </a>
                                        @endforeach
                                    @endif
                                @endforeach
                            </nav>
                        </div>
                    </aside>
                @endif
            @endauth

            <main class="panel-contenido py-4">
                <div class="container">
                    @include('partials.alertas')
                </div>

                @yield('content')
            </main>
        </div>
    </div>

    {{-- Scripts adicionales de las vistas --}}
    @stack('scripts')

</body>
</html>