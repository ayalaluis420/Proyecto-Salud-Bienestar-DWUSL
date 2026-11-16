<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForoPost extends Model
{
    public const CATEGORIAS = ['Ejercicio', 'Dieta', 'Sueño', 'Bienestar', 'Otro'];

    protected $fillable = ['categoria', 'titulo', 'contenido'];

    protected function casts(): array
    {
        return ['oculto' => 'boolean'];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(ForoComentario::class);
    }

    public function scopeVisibles(Builder $q): Builder
    {
        return $q->where('oculto', false);
    }

    public function scopeFiltrar(Builder $q, array $f): Builder
    {
        $buscar = trim((string) ($f['buscar'] ?? ''));

        return $q
            ->when(! empty($f['categoria']) && in_array($f['categoria'], self::CATEGORIAS, true),
                fn (Builder $w) => $w->where('categoria', $f['categoria']))
            ->when(($f['estado'] ?? null) === 'visible', fn (Builder $w) => $w->where('oculto', false))
            ->when(($f['estado'] ?? null) === 'oculto', fn (Builder $w) => $w->where('oculto', true))
            ->when($buscar !== '', function (Builder $w) use ($buscar) {
                $like = '%'.addcslashes($buscar, '\\%_').'%';
                $w->where(fn (Builder $x) => $x->where('titulo', 'like', $like)->orWhere('contenido', 'like', $like));
            });
    }
}