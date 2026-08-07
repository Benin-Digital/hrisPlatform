<?php

namespace App\Console\Commands;

use App\Models\Pointage;
use Illuminate\Console\Command;

class CorrigerRetards extends Command
{
    protected $signature = 'pointage:corriger-retards';
    protected $description = 'Corrige les statuts des pointages qui devraient être en retard';

    public function handle()
    {
        $seuil = '08:30:00';
        $today = now()->toDateString();

        $pointages = Pointage::whereDate('date', $today)
            ->where('statut', 'present')
            ->where('heure_entree', '>', $seuil)
            ->get();

        foreach ($pointages as $p) {
            $p->minutes_retard = $p->calculerRetard();
            $p->statut = 'retard';
            $p->save();
        }

        $this->info('Retards corrigés pour ' . $pointages->count() . ' pointages.');
    }
}