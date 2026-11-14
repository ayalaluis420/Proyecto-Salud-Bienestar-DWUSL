<?php

namespace Database\Seeders;

use App\Models\Alimento;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlanificacionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['Manzana', 'frutas', 52, 100], ['Arroz cocido', 'cereales', 130, 100], ['Pechuga de pollo', 'proteinas', 165, 100], ['Yogur natural', 'lacteos', 61, 100], ['Brócoli', 'verduras', 34, 100]] as [$nombre,$categoria,$calorias,$porcion]) {
            Alimento::firstOrCreate(['nombre' => $nombre], ['categoria' => $categoria, 'calorias' => $calorias, 'porcion_gramos' => $porcion]);
        }
        // Los registros personales de demostración solo se crean en desarrollo/pruebas.
        if (! app()->environment(['local', 'testing'])) {
            return;
        }
        $u = User::where('email', 'usuario@salud.test')->first();
        if (! $u) {
            return;
        }
        foreach ([['Caminar 100 km este mes', 100, 35, today()->addDays(15)], ['Completar 12 sesiones', 12, 12, today()->subDay()], ['Beber agua durante 7 días', 7, 3, today()->subDays(2)]] as [$titulo,$meta,$actual,$fin]) {
            $u->objetivos()->firstOrCreate(['titulo' => $titulo], ['unidad' => 'unidades', 'valor_meta' => $meta, 'valor_actual' => $actual, 'fecha_inicio' => today()->subDays(15), 'fecha_fin' => $fin]);
        }
        $r = $u->recordatorios()->firstOrCreate(['nombre' => 'Vitamina de demostración'], ['tipo' => 'vitamina', 'dosis' => '1 unidad (dato ficticio)', 'hora' => '08:00', 'frecuencia' => 'diaria', 'fecha_inicio' => today()->subDays(6), 'activo' => true]);
        foreach ([0, 1, 2, 4] as $dias) {
            $r->cumplimientos()->firstOrCreate(['fecha' => today()->subDays($dias)->toDateString()]);
        }
        $u->recordatorios()->firstOrCreate(['nombre' => 'Medicamento de demostración'], ['tipo' => 'medicamento', 'dosis' => 'Según indicación (dato ficticio)', 'hora' => '19:30', 'frecuencia' => 'semanal', 'fecha_inicio' => today()->subDays(7), 'activo' => true]);
        $p = $u->planesComida()->firstOrCreate(['fecha' => today()->toDateString(), 'tipo' => 'almuerzo'], ['notas' => 'Plan de ejemplo para la demostración del panel.']);
        foreach (Alimento::whereIn('nombre', ['Arroz cocido', 'Pechuga de pollo', 'Brócoli'])->get() as $a) {
            $p->detalles()->firstOrCreate(['alimento_id' => $a->id], ['porciones' => 1, 'calorias_porcion' => $a->calorias]);
        }
    }
}
