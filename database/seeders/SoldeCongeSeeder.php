<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SoldeConge;
use App\Models\Utilisateur;

class SoldeCongeSeeder extends Seeder
{
    public function run()
    {
        $users = Utilisateur::where('type', 'interne')->get();
        foreach ($users as $user) {
            SoldeConge::firstOrCreate([
                'utilisateur_id' => $user->id,
                'type_conge' => 'annuel',
                'annee' => date('Y'),
            ], [
                'solde_initial' => 25,
                'solde_pris' => 0,
                'solde_restant' => 25,
            ]);
        }
    }
}