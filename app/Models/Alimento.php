<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alimento extends Model
{
    protected $fillable = ['nombre', 'categoria', 'calorias', 'porcion_gramos'];

    public const CATEGORIAS = ['frutas', 'verduras', 'cereales', 'proteinas', 'lacteos', 'grasas', 'bebidas', 'otros'];

    protected function casts(): array
    {
        return ['calorias' => 'float', 'porcion_gramos' => 'float'];
    }

    public function detalles()
    {
        return $this->hasMany(PlanComidaDetalle::class);
    }
}
