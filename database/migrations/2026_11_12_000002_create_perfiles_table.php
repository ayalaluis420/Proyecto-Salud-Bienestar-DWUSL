<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Miembro 1 – Perfil de salud del usuario (1 a 1 con users).
 *
 * altura y peso son datos sensibles: se guardan CIFRADOS (Crypt/APP_KEY),
 * por eso son columnas TEXT y no numéricas. Ver App\Casts\DecimalCifrado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('edad')->nullable();
            $table->string('sexo', 10)->nullable();   // masculino | femenino | otro
            $table->text('altura')->nullable();       // cm  (cifrado)
            $table->text('peso')->nullable();         // kg  (cifrado)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfiles');
    }
};
