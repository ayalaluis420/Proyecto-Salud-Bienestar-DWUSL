<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoEjercicio extends Model
{
    protected $table = 'tipos_ejercicio';

    protected $fillable = [
        'nombre',
        'descripcion',
        'calorias_por_minuto',
        'activo'
    ];

    protected function casts(): array
    {
        return [
            'calorias_por_minuto' => 'decimal:2',
            'activo' => 'boolean'
        ];
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(EntrenamientoDetalle::class);
    }
}