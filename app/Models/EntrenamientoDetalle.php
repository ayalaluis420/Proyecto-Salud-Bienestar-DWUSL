<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EntrenamientoDetalle extends Model
{
    use HasFactory;
    
    protected $table = 'entrenamiento_detalles';

    protected $fillable = [
        'entrenamiento_id',
        'tipo_ejercicio_id',
        'duracion_min',
        'intensidad',
        'calorias'
    ];

    protected function casts(): array
    {
        return [
            'duracion_min' => 'integer',
            'calorias' => 'decimal:2'
        ];
    }

    public function entrenamiento(): BelongsTo
    {
        return $this->belongsTo(Entrenamiento::class);
    }

    public function tipoEjercicio(): BelongsTo
    {
        return $this->belongsTo(TipoEjercicio::class);
    }
}
