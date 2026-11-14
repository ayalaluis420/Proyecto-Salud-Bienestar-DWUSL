<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanComidaDetalle extends Model
{
    protected $fillable = ['alimento_id', 'porciones', 'calorias_porcion'];

    protected function casts(): array
    {
        return ['porciones' => 'float', 'calorias_porcion' => 'float'];
    }

    public function alimento()
    {
        return $this->belongsTo(Alimento::class);
    }

    public function planComida()
    {
        return $this->belongsTo(PlanComida::class);
    }
}
