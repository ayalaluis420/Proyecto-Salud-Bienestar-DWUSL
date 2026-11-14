<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActividadFisica extends Model
{
    use HasFactory;
    
    protected $table = 'actividades_fisicas';

    protected $fillable = [
        'user_id',
        'fecha',
        'pasos',
        'distancia_km',
        'calorias'
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'pasos' => 'integer',
            'distancia_km' => 'decimal:2',
            'calorias' => 'decimal:2'
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
