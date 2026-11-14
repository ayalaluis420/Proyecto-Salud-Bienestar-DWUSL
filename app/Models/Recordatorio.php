<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Recordatorio extends Model
{
    protected $fillable = ['nombre', 'tipo', 'dosis', 'hora', 'frecuencia', 'fecha_inicio', 'fecha_fin', 'activo'];

    protected function casts(): array
    {
        return ['fecha_inicio' => 'date:Y-m-d', 'fecha_fin' => 'date:Y-m-d', 'activo' => 'boolean'];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cumplimientos()
    {
        return $this->hasMany(RecordatorioCumplimiento::class);
    }

    public function programadoEn($fecha): bool
    {
        $dia = Carbon::parse($fecha)->startOfDay();
        if ($dia->lt($this->fecha_inicio) || ($this->fecha_fin && $dia->gt($this->fecha_fin))) {
            return false;
        }

        return match ($this->frecuencia) {
            'diaria' => true,
            'semanal' => $dia->dayOfWeek === $this->fecha_inicio->dayOfWeek,
            'mensual' => $dia->day === min($this->fecha_inicio->day, $dia->daysInMonth),
            default => false,
        };
    }
}
