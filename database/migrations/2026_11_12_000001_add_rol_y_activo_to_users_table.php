<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Miembro 1 – Usuarios, autenticación y seguridad.
 * Agrega el rol (admin | moderador | usuario) y el estado activo/inactivo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rol', 20)->default('usuario')->after('password')->index();
            $table->boolean('activo')->default(true)->after('rol')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['rol']);
            $table->dropIndex(['activo']);
            $table->dropColumn(['rol', 'activo']);
        });
    }
};
