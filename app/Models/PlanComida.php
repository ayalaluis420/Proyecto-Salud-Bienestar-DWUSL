<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanComida extends Model
{
    protected $table = 'planes_comida';

    protected $fillable = ['fecha', 'tipo', 'notas'];

    protected $appends = ['calorias_totales'];

    protected function casts(): array
    {
        return ['fecha' => 'date:Y-m-d'];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles()
    {
        return $this->hasMany(PlanComidaDetalle::class);
    }

    public function getCaloriasTotalesAttribute(): float
    {
        return round($this->detalles->sum(fn ($d) => $d->porciones * $d->calorias_porcion), 2);
    }
}
