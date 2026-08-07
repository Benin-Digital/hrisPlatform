<?php

namespace App\Console\Commands;

use App\Models\Pointage;
use App\Models\Utilisateur;
use Illuminate\Console\Command;

class MarquerRetard extends Command
{
    protected $signature = 'pointage:retard';
    protected $description = 'Marque comme retard les collaborateurs n\'ayant pas pointé avant 8h30';

    public function handle()
    {
        $today = now()->toDateString();
        $users = Utilisateur::where('statut', 'actif')->get();

        foreach ($users as $user) {
            if ($user->estExempte($today)) continue;

            $pointage = Pointage::where('utilisateur_id', $user->id)
                ->where('date', $today)
                ->first();

            if (!$pointage || is_null($pointage->heure_entree)) {
                if (!$pointage) {
                    Pointage::create([
                        'utilisateur_id' => $user->id,
                        'date' => $today,
                        'statut' => 'retard',
                    ]);
                } else {
                    $pointage->statut = 'retard';
                    $pointage->save();
                }
            }
        }

        $this->info('Collaborateurs marqués comme retard.');
        return 0;
    }
}