<?php

namespace App\Console\Commands;

use App\Models\Pointage;
use App\Models\Utilisateur;
use Illuminate\Console\Command;

class MarquerAbsent extends Command
{
    protected $signature = 'pointage:absent';
    protected $description = 'Marque comme absent les collaborateurs n\'ayant pas pointé avant 18h';

    public function handle()
    {
        $today = now()->toDateString();
        $users = Utilisateur::where('statut', 'actif')->get();

        foreach ($users as $user) {
            if ($user->estExempte($today)) continue;

            $pointage = Pointage::where('utilisateur_id', $user->id)
                ->where('date', $today)
                ->first();

            // Si pas de pointage ou pas d'heure d'entrée
            if (!$pointage || is_null($pointage->heure_entree)) {
                if (!$pointage) {
                    Pointage::create([
                        'utilisateur_id' => $user->id,
                        'date' => $today,
                        'statut' => 'absent',
                    ]);
                } else {
                    // Si le pointage existe mais n'a pas d'heure d'entrée
                    $pointage->statut = 'absent';
                    $pointage->save();
                }
            }
        }

        $this->info('Collaborateurs marqués comme absents.');
        return 0;
    }
}