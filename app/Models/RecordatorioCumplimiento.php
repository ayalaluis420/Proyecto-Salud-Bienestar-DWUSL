<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordatorioCumplimiento extends Model
{
    protected $fillable = ['fecha'];

    protected function casts(): array
    {
        return ['fecha' => 'date:Y-m-d'];
    }

    public function recordatorio()
    {
        return $this->belongsTo(Recordatorio::class);
    }
}
