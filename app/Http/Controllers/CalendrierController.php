<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use App\Models\Formation;
use App\Models\Evenement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendrierController extends Controller
{
    public function getEvenements()
    {
        $user = Auth::user();

        $evenements = [];

        // 1. Congés de l'utilisateur (ou de son entité si manager)
        $conges = Conge::with('utilisateur')
            ->where('statut', 'valide')
            ->when(!$user->hasRole('super_admin'), function ($q) use ($user) {
                $q->whereHas('utilisateur', function ($sub) use ($user) {
                    $sub->where('entite_id', $user->entite_id);
                });
            })
            ->get();

        foreach ($conges as $conge) {
            $evenements[] = [
                'id' => 'conge_' . $conge->id,
                'title' => 'Congé - ' . $conge->utilisateur->prenom . ' ' . $conge->utilisateur->nom,
                'start' => $conge->date_debut->format('Y-m-d'),
                'end' => $conge->date_fin->addDay()->format('Y-m-d'), // FullCalendar exclusive end
                'color' => '#3b82f6',
                'extendedProps' => [
                    'type' => 'conge',
                    'statut' => $conge->statut,
                ],
            ];
        }

        // 2. Formations (publiques)
        $formations = Formation::where('statut', 'publie')
            ->where('date_debut', '>=', now())
            ->get();

        foreach ($formations as $formation) {
            $evenements[] = [
                'id' => 'formation_' . $formation->id,
                'title' => 'Formation : ' . $formation->titre,
                'start' => $formation->date_debut?->format('Y-m-d'),
                'end' => $formation->date_fin?->addDay()->format('Y-m-d'),
                'color' => '#10b981',
                'extendedProps' => [
                    'type' => 'formation',
                ],
            ];
        }

        // 3. Événements internes
        $evenementsInternes = Evenement::where('date_debut', '>=', now())
            ->where('statut', 'publie')
            ->get();

        foreach ($evenementsInternes as $event) {
            $evenements[] = [
                'id' => 'event_' . $event->id,
                'title' => $event->titre,
                'start' => $event->date_debut->format('Y-m-d'),
                'end' => $event->date_fin?->addDay()->format('Y-m-d'),
                'color' => '#f59e0b',
                'extendedProps' => [
                    'type' => 'evenement',
                ],
            ];
        }

        return response()->json($evenements);
    }
}