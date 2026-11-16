<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foro_posts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('categoria', 20);
            $t->string('titulo', 80);
            $t->text('contenido');
            $t->boolean('oculto')->default(false);     // moderación: oculto = no sale en la API
            $t->timestamps();
            $t->index(['oculto', 'created_at']);
        });

        Schema::create('foro_comentarios', function (Blueprint $t) {
            $t->id();
            $t->foreignId('foro_post_id')->constrained('foro_posts')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('contenido', 500);
            $t->boolean('oculto')->default(false);
            $t->timestamps();
            $t->index(['foro_post_id', 'oculto']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foro_comentarios');
        Schema::dropIfExists('foro_posts');
    }
};