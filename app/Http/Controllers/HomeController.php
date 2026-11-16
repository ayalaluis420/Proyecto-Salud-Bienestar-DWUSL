<?php

namespace App\Http\Controllers;

use App\Models\Consejo;
use App\Models\Entrenamiento;
use App\Models\ForoComentario;
use App\Models\ForoPost;
use App\Models\Objetivo;
use App\Models\Recordatorio;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/** Dashboard del panel (Miembro 4): resumen de todos los módulos. */
class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $desde = today()->subDays(6); // últimos 7 días, contando hoy

        $resumen = [
            'usuarios'          => User::where('rol', User::ROL_USUARIO)->count(),
            'usuarios_activos'  => User::where('rol', User::ROL_USUARIO)->where('activo', true)->count(),
            'posts'             => ForoPost::count(),
            'posts_ocultos'     => ForoPost::where('oculto', true)->count(),
            'comentarios'       => ForoComentario::count(),
            'consejos'          => Consejo::count(),
            'consejos_public'   => Consejo::where('publicado', true)->count(),
            'entrenamientos_7d' => Entrenamiento::where('fecha', '>=', $desde)->count(),
            'recordatorios'     => Recordatorio::where('activo', true)->count(),
        ];

        $objetivos = [
            'En curso'  => Objetivo::estado('en_curso')->count(),
            'Cumplidos' => Objetivo::estado('cumplido')->count(),
            'Vencidos'  => Objetivo::estado('vencido')->count(),
        ];

        // Actividad del foro por día. Se agrupa en PHP para no depender de
        // funciones de fecha propias de MySQL (los tests corren en otra base).
        $dias = collect(range(0, 6))->map(fn (int $i) => $desde->copy()->addDays($i));
        $porDia = fn (string $modelo) => $modelo::where('created_at', '>=', $desde)
            ->pluck('created_at')
            ->countBy(fn (Carbon $f) => $f->toDateString());
        $postsDia = $porDia(ForoPost::class);
        $comentariosDia = $porDia(ForoComentario::class);

        $actividad = [
            'etiquetas'   => $dias->map(fn (Carbon $d) => $d->format('d/m'))->all(),
            'posts'       => $dias->map(fn (Carbon $d) => $postsDia[$d->toDateString()] ?? 0)->all(),
            'comentarios' => $dias->map(fn (Carbon $d) => $comentariosDia[$d->toDateString()] ?? 0)->all(),
        ];

        $conteo = ForoPost::selectRaw('categoria, COUNT(*) as total')->groupBy('categoria')->pluck('total', 'categoria');
        $categorias = [
            'etiquetas' => ForoPost::CATEGORIAS,
            'totales'   => array_map(fn (string $c) => (int) ($conteo[$c] ?? 0), ForoPost::CATEGORIAS),
        ];

        $masVistos = Consejo::where('publicado', true)->where('vistas', '>', 0)
            ->orderByDesc('vistas')->orderBy('id')->take(5)->get(['id', 'titulo', 'categoria', 'vistas']);

        $ultimosPosts = ForoPost::with('autor:id,name')->latest('id')->take(5)->get();

        return view('home', compact('resumen', 'objetivos', 'actividad', 'categorias', 'masVistos', 'ultimosPosts'));
    }
}