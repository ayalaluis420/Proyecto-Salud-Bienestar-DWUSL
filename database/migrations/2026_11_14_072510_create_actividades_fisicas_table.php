<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividades_fisicas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->date('fecha');
            $table->unsignedInteger('pasos')->default(0);
            $table->decimal('distancia_km', 8, 2)->default(0);
            $table->decimal('calorias', 8, 2)->default(0);

            $table->timestamps();

            $table->unique(['user_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividades_fisicas');
    }
};
