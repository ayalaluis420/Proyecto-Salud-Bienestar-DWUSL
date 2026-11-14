<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('objetivos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('titulo', 150);
            $t->text('descripcion')->nullable();
            $t->string('unidad', 30);
            $t->decimal('valor_meta', 12, 2);
            $t->decimal('valor_actual', 12, 2)->default(0);
            $t->date('fecha_inicio');
            $t->date('fecha_fin');
            $t->timestamps();
            $t->index(['user_id', 'fecha_fin']);
        });
        Schema::create('recordatorios', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('nombre', 150);
            $t->string('tipo', 20);
            $t->string('dosis', 100);
            $t->time('hora');
            $t->string('frecuencia', 20);
            $t->date('fecha_inicio');
            $t->date('fecha_fin')->nullable();
            $t->boolean('activo')->default(true);
            $t->timestamps();
        });
        Schema::create('recordatorio_cumplimientos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('recordatorio_id')->constrained()->cascadeOnDelete();
            $t->date('fecha');
            $t->timestamps();
            $t->unique(['recordatorio_id', 'fecha']);
        });
        Schema::create('alimentos', function (Blueprint $t) {
            $t->id();
            $t->string('nombre', 150)->unique();
            $t->string('categoria', 50);
            $t->decimal('calorias', 10, 2);
            $t->decimal('porcion_gramos', 10, 2)->default(100);
            $t->timestamps();
        });
        Schema::create('planes_comida', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->date('fecha');
            $t->string('tipo', 20);
            $t->text('notas')->nullable();
            $t->timestamps();
            $t->unique(['user_id', 'fecha', 'tipo']);
        });
        Schema::create('plan_comida_detalles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('plan_comida_id')->constrained('planes_comida')->cascadeOnDelete();
            $t->foreignId('alimento_id')->constrained()->restrictOnDelete();
            $t->decimal('porciones', 8, 2);
            $t->decimal('calorias_porcion', 10, 2);
            $t->timestamps();
            $t->unique(['plan_comida_id', 'alimento_id']);
        });
    }

    public function down(): void
    {
        foreach (['plan_comida_detalles', 'planes_comida', 'alimentos', 'recordatorio_cumplimientos', 'recordatorios', 'objetivos'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
