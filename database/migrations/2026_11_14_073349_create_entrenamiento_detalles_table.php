<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrenamiento_detalles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('entrenamiento_id')
                  ->constrained('entrenamientos')
                  ->cascadeOnDelete();

            $table->foreignId('tipo_ejercicio_id')
                  ->constrained('tipos_ejercicio')
                  ->restrictOnDelete();

            $table->unsignedSmallInteger('duracion_min');
            $table->string('intensidad', 20);
            $table->decimal('calorias', 8, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrenamiento_detalles');
    }
};
