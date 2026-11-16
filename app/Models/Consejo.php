<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consejo extends Model
{
    public const CATEGORIAS = [
        'dieta'     => 'Dieta',
        'sueno'     => 'Sueño',
        'bienestar' => 'Bienestar',
    ];

    protected $fillable = ['titulo', 'categoria', 'contenido', 'publicado'];

    protected function casts(): array
    {
        return ['publicado' => 'boolean'];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getNombreCategoriaAttribute(): string
    {
        return self::CATEGORIAS[$this->categoria] ?? ucfirst((string) $this->categoria);
    }

    public function scopePublicados(Builder $q): Builder
    {
        return $q->where('publicado', true);
    }

    public function scopeFiltrar(Builder $q, array $f): Builder
    {
        $buscar = trim((string) ($f['buscar'] ?? ''));

        return $q
            ->when(! empty($f['categoria']) && array_key_exists($f['categoria'], self::CATEGORIAS),
                fn (Builder $w) => $w->where('categoria', $f['categoria']))
            ->when(($f['estado'] ?? null) === 'publicado', fn (Builder $w) => $w->where('publicado', true))
            ->when(($f['estado'] ?? null) === 'oculto', fn (Builder $w) => $w->where('publicado', false))
            ->when($buscar !== '', function (Builder $w) use ($buscar) {
                $like = '%'.addcslashes($buscar, '\\%_').'%';
                $w->where(fn (Builder $x) => $x->where('titulo', 'like', $like)->orWhere('contenido', 'like', $like));
            });
    }
}