<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consejos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // quién lo creó
            $t->string('titulo', 150);
            $t->string('categoria', 20);               // dieta, sueno, bienestar
            $t->text('contenido');
            $t->boolean('publicado')->default(true);   // false = oculto en la app
            $t->unsignedInteger('vistas')->default(0); // para "más consultados"
            $t->timestamps();
            $t->index(['publicado', 'categoria']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consejos');
    }
};