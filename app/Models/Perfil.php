<?php

namespace App\Models;

use App\Casts\DecimalCifrado;
use Database\Factories\PerfilFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Perfil extends Model
{
    /** @use HasFactory<PerfilFactory> */
    use HasFactory;

    protected $table = 'perfiles';

    public const SEXOS = ['masculino', 'femenino', 'otro'];

    protected $fillable = ['user_id', 'edad', 'sexo', 'altura', 'peso'];

    protected function casts(): array
    {
        return [
            'edad'   => 'integer',
            'altura' => DecimalCifrado::class,   // cm, cifrado en BD
            'peso'   => DecimalCifrado::class,   // kg, cifrado en BD
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Índice de masa corporal (kg / m²) o null si faltan datos. */
    public function getImcAttribute(): ?float
    {
        if (! $this->peso || ! $this->altura) {
            return null;
        }

        $metros = $this->altura / 100;

        return round($this->peso / ($metros * $metros), 1);
    }
}
