<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Entrenamiento extends Model
{
    use HasFactory;
    
    protected $table = 'entrenamientos';

    protected $fillable = [
        'user_id',
        'fecha',
        'observaciones'
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime'
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(EntrenamientoDetalle::class);
    }
}
