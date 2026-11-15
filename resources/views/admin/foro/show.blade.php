@extends('layouts.app')

@section('title', 'Publicación #'.$post->id)

@section('content')
<div class="container">
    <a href="{{ route('foro.index') }}" class="btn btn-sm btn-outline-secondary mb-3">&larr; Volver al foro</a>

    <div class="card mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <span class="badge text-bg-secondary">{{ $post->categoria }}</span>
                @if ($post->oculto)
                    <span class="badge text-bg-dark">Oculta</span>
                @else
                    <span class="badge text-bg-success">Visible</span>
                @endif
            </div>
            <div class="d-inline-flex gap-1">
                <form method="POST" action="{{ route('foro.visibilidad', $post) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm {{ $post->oculto ? 'btn-outline-success' : 'btn-outline-warning' }}">
                        {{ $post->oculto ? 'Mostrar' : 'Ocultar' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('foro.destroy', $post) }}"
                      data-confirm="¿Eliminar esta publicación y todos sus comentarios? Esta acción no se puede deshacer.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <h1 class="h4">{{ $post->titulo }}</h1>
            <p class="text-muted small mb-3">
                Por <strong>{{ $post->autor?->name ?? 'usuario eliminado' }}</strong>
                · {{ $post->created_at->format('d/m/Y H:i') }}
            </p>
            {{-- {{ }} escapa el HTML: el contenido de los usuarios nunca se interpreta como código --}}
            <p class="mb-0" style="white-space: pre-line;">{{ $post->contenido }}</p>
        </div>
    </div>

    <h2 class="h5">Comentarios ({{ $post->comentarios->count() }})</h2>

    @forelse ($post->comentarios as $c)
        <div class="card mb-2 {{ $c->oculto ? 'border-dark' : '' }}">
            <div class="card-body py-2 d-flex flex-wrap justify-content-between align-items-start gap-2">
                <div class="flex-grow-1">
                    <div class="small text-muted">
                        <strong>{{ $c->autor?->name ?? 'usuario eliminado' }}</strong>
                        · {{ $c->created_at->format('d/m/Y H:i') }}
                        @if ($c->oculto)
                            <span class="badge text-bg-dark">Oculto</span>
                        @endif
                    </div>
                    <div style="white-space: pre-line;">{{ $c->contenido }}</div>
                </div>
                <div class="d-inline-flex gap-1">
                    <form method="POST" action="{{ route('foro.comentarios.visibilidad', $c) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm {{ $c->oculto ? 'btn-outline-success' : 'btn-outline-warning' }}">
                            {{ $c->oculto ? 'Mostrar' : 'Ocultar' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('foro.comentarios.destroy', $c) }}"
                          data-confirm="¿Eliminar este comentario? Esta acción no se puede deshacer.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <p class="text-muted">Esta publicación aún no tiene comentarios.</p>
    @endforelse
</div>
@endsection