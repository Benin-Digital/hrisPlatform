<?php

namespace App\Console\Commands;

use App\Models\Pointage;
use App\Models\Utilisateur;
use App\Notifications\RappelPointage;
use Illuminate\Console\Command;

class RappelPointageMatin extends Command
{
    protected $signature = 'pointage:rappel-matin';
    protected $description = 'Envoie un rappel aux collaborateurs n\'ayant pas pointé avant 8h30';

    public function handle()
    {
        $today = now()->toDateString();
        $users = Utilisateur::where('statut', 'actif')->get();
        $sansPointage = [];

        foreach ($users as $user) {
            // Ignorer les exemptés (congés, etc.)
            if ($user->estExempte($today)) continue;

            $pointage = Pointage::where('utilisateur_id', $user->id)
                ->where('date', $today)
                ->first();

            if (!$pointage || is_null($pointage->heure_entree)) {
                $sansPointage[] = $user;
            }
        }

        foreach ($sansPointage as $user) {
            $user->notify(new RappelPointage());
        }

        $this->info('Rappel envoyé à ' . count($sansPointage) . ' collaborateurs.');
        return 0;
    }
}