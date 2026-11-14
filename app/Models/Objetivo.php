<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Objetivo extends Model
{
    protected $fillable = ['titulo', 'descripcion', 'unidad', 'valor_meta', 'valor_actual', 'fecha_inicio', 'fecha_fin'];

    protected $appends = ['estado', 'porcentaje'];

    protected function casts(): array
    {
        return ['valor_meta' => 'float', 'valor_actual' => 'float', 'fecha_inicio' => 'date:Y-m-d', 'fecha_fin' => 'date:Y-m-d'];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getEstadoAttribute(): string
    {
        if ($this->valor_actual >= $this->valor_meta) {
            return 'cumplido';
        }

        return $this->fecha_fin->lt(today()) ? 'vencido' : 'en_curso';
    }

    public function getPorcentajeAttribute(): float
    {
        return $this->valor_meta > 0 ? round(min(100, $this->valor_actual / $this->valor_meta * 100), 1) : 0;
    }

    public function scopeEstado($q, ?string $estado)
    {
        return match ($estado) {
            'cumplido' => $q->whereColumn('valor_actual', '>=', 'valor_meta'),
            'vencido' => $q->whereColumn('valor_actual', '<', 'valor_meta')->whereDate('fecha_fin', '<', today()),
            'en_curso' => $q->whereColumn('valor_actual', '<', 'valor_meta')->whereDate('fecha_fin', '>=', today()),
            default => $q,
        };
    }
}
