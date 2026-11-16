<?php

namespace Database\Seeders;

use App\Models\Consejo;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Miembro 4 – Consejos de salud de ejemplo (dieta, sueño y bienestar). */
class ConsejoSeeder extends Seeder
{
    public function run(): void
    {
        if (Consejo::exists()) {
            return; // evita duplicar datos si se ejecuta más de una vez
        }

        $autor = User::where('rol', User::ROL_ADMIN)->first();

        $consejos = [
            ['dieta', 'Toma suficiente agua durante el día', 'Procura beber entre seis y ocho vasos de agua al día. Llevar una botella contigo te ayuda a mantener el hábito y a evitar confundir la sed con hambre.', true, 42],
            ['dieta', 'Incluye frutas y verduras en cada comida', 'Intenta que la mitad de tu plato sean verduras o frutas. Aportan fibra, vitaminas y te mantienen saciado por más tiempo.', true, 35],
            ['dieta', 'Reduce las bebidas azucaradas', 'Cambia gaseosas y jugos industriales por agua, agua con limón o infusiones sin azúcar. Es uno de los cambios más sencillos para cuidar tu salud.', true, 28],
            ['sueno', 'Mantén un horario de sueño regular', 'Acostarte y levantarte a la misma hora, incluso los fines de semana, ayuda a que tu cuerpo regule el descanso y a dormir mejor.', true, 51],
            ['sueno', 'Evita las pantallas antes de dormir', 'Apaga el celular y la televisión al menos 30 minutos antes de acostarte. La luz de las pantallas dificulta conciliar el sueño.', true, 30],
            ['sueno', 'Crea un ambiente tranquilo para descansar', 'Procura que tu habitación esté oscura, fresca y silenciosa. Un ambiente cómodo mejora la calidad del sueño profundo.', true, 18],
            ['bienestar', 'Haz pausas activas durante la jornada', 'Cada hora, levántate, estira el cuello, los hombros y la espalda durante cinco minutos. Reduce la tensión muscular y mejora la concentración.', true, 47],
            ['bienestar', 'Dedica unos minutos a respirar y relajarte', 'Practica respiración profunda durante tres a cinco minutos al día. Inhala por la nariz, sostén un momento y exhala lento para reducir el estrés.', true, 25],
            ['bienestar', 'Consejo en revisión (oculto)', 'Este consejo está oculto para que puedas probar la opción de publicar y ocultar desde el panel de administración.', false, 0],
        ];

        foreach ($consejos as [$categoria, $titulo, $contenido, $publicado, $vistas]) {
            $c = new Consejo(compact('categoria', 'titulo', 'contenido'));
            $c->publicado = $publicado;
            $c->vistas = $vistas;
            $c->user_id = $autor?->id;
            $c->save();
        }
    }
}