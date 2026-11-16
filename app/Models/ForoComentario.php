<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForoComentario extends Model
{
    protected $table = 'foro_comentarios';

    protected $fillable = ['contenido'];

    protected function casts(): array
    {
        return ['oculto' => 'boolean'];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(ForoPost::class, 'foro_post_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeVisibles(Builder $q): Builder
    {
        return $q->where('oculto', false);
    }
}