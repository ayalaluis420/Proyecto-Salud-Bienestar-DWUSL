<?php

namespace Database\Seeders;

use App\Models\ForoComentario;
use App\Models\ForoPost;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Miembro 4 – Publicaciones y comentarios de prueba para el foro. */
class ForoSeeder extends Seeder
{
    public function run(): void
    {
        if (ForoPost::exists()) {
            return; // evita duplicar datos si se ejecuta más de una vez
        }

        $usuarios = User::where('rol', User::ROL_USUARIO)->where('activo', true)->take(6)->get();
        if ($usuarios->count() < 2) {
            return;
        }

        $posts = [
            ['Ejercicio', 'Rutina de 20 minutos en casa', 'Estoy empezando con ejercicios sin equipo: sentadillas, planchas y saltos. ¿Alguien tiene más ideas para variar la rutina?'],
            ['Dieta', '¿Cómo reducir el azúcar?', 'Quiero bajar el consumo de azúcar sin sentir ansiedad. ¿Qué cambios les han funcionado?'],
            ['Sueño', 'No logro dormir antes de medianoche', 'Llevo semanas durmiendo tarde. ¿Qué hábitos les ayudaron a ordenar su horario de sueño?'],
            ['Bienestar', 'Pausas activas en el trabajo', 'Paso muchas horas sentado. Estoy probando pausas de 5 minutos cada hora para estirarme.'],
            ['Ejercicio', 'Meta de 8,000 pasos diarios', 'Esta semana llegué a mi meta cuatro días. Les comparto que caminar después de almorzar ayuda mucho.'],
            ['Otro', 'Contenido de prueba para moderar', 'Esta publicación sirve para probar el botón de ocultar y eliminar del panel de moderación.'],
        ];

        $comentarios = [
            'Me sirvió mucho, gracias por compartirlo.',
            'Yo hago algo parecido y noto la diferencia.',
            'Buena idea, lo voy a intentar esta semana.',
            '¿Cuánto tiempo llevas con eso?',
        ];

        foreach ($posts as $i => [$categoria, $titulo, $contenido]) {
            $post = new ForoPost(compact('categoria', 'titulo', 'contenido'));
            $post->user_id = $usuarios[$i % $usuarios->count()]->id;
            $post->oculto = $i === 5; // uno ya oculto para ver el estado en el panel
            $post->save();

            foreach (array_slice($comentarios, 0, ($i % 3) + 1) as $j => $texto) {
                $c = new ForoComentario(['contenido' => $texto]);
                $c->foro_post_id = $post->id;
                $c->user_id = $usuarios[($i + $j + 1) % $usuarios->count()]->id;
                $c->save();
            }
        }
    }
}