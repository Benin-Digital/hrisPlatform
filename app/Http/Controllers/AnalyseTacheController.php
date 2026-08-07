<?php

namespace App\Http\Controllers;

use App\Models\Tache;
use App\Models\EspaceCollaboratif;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyseTacheController extends Controller
{
    /**
     * Récupère les statistiques de productivité pour un espace donné.
     */
    public function getEspaceStats($id)
    {
        $espace = EspaceCollaboratif::findOrFail($id);

        $totalTaches = Tache::where('espace_id', $id)->count();
        $terminees = Tache::where('espace_id', $id)->where('statut', 'terminee')->count();
        
        $statsStatut = Tache::where('espace_id', $id)
            ->select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->get();

        $statsPriorite = Tache::where('espace_id', $id)
            ->select('priorite', DB::raw('count(*) as total'))
            ->groupBy('priorite')
            ->get();

        // Analyse délais (Respect des échéances)
        $respectDelais = Tache::where('espace_id', $id)
            ->where('statut', 'terminee')
            ->whereNotNull('date_echeance')
            ->whereNotNull('date_fin_reelle')
            ->select(DB::raw('COUNT(*) as total'), 
                     DB::raw('SUM(CASE WHEN date_fin_reelle <= date_echeance THEN 1 ELSE 0 END) as a_temps'))
            ->first();

        // Analyse Charge de travail vs Estimation
        $chargeTravail = Tache::where('espace_id', $id)
            ->where('statut', 'terminee')
            ->select(DB::raw('SUM(estimation_heures) as total_estime'), 
                     DB::raw('SUM(temps_passe_minutes) / 60 as total_reel'))
            ->first();

        return response()->json([
            'total' => $totalTaches,
            'taux_completion' => $totalTaches > 0 ? round(($terminees / $totalTaches) * 100, 2) : 0,
            'stats_statut' => $statsStatut,
            'stats_priorite' => $statsPriorite,
            'respect_delais' => $respectDelais,
            'charge_travail' => $chargeTravail,
        ]);
    }

    /**
     * Récupère les statistiques pour un collaborateur spécifique (Global ou Espace).
     */
    public function getCollaborateurStats(Request $request, $id)
    {
        $query = Tache::where('assigne_a', $id);

        if ($request->has('espace_id')) {
            $query->where('espace_id', $request->espace_id);
        }

        $stats = $query->select(
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN statut = "terminee" THEN 1 ELSE 0 END) as terminees'),
            DB::raw('SUM(estimation_heures) as heures_estimees'),
            DB::raw('SUM(temps_passe_minutes) / 60 as heures_reelles')
        )->first();

        return response()->json($stats);
    }

    public function update(Request $request, $id)
{
    $tache = Tache::findOrFail($id);
    $ancienAssigne = $tache->assigne_a;

    // ... validation et mise à jour

    // Si l'assignation a changé
    if ($tache->assigne_a && $tache->assigne_a !== $ancienAssigne) {
        $nouvelAssigné = Utilisateur::find($tache->assigne_a);
        if ($nouvelAssigné) {
            $nouvelAssigné->notify(new \App\Notifications\TacheAssignee($tache));
            
            // Diffusion Reverb
            $notification = (object) [
                'id' => uniqid(),
                'data' => [
                    'message' => "La tâche \"{$tache->titre}\" vous a été réassignée.",
                    'tache_id' => $tache->id,
                    'titre' => $tache->titre,
                ],
                'created_at' => now(),
            ];
            broadcast(new NouvelleNotification($notification, $nouvelAssigné->id));
        }
    }
}
}
