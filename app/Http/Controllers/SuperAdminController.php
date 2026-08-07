<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Models\Entite;
use App\Models\Tache;
use App\Models\Annonce;      // ← Ajout
use App\Models\Evenement;    // ← Ajout
use Illuminate\Http\Request;
use Inertia\Inertia;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        // Stats existantes
        $stats = [
            'totalUsers'     => Utilisateur::count(),
            'activeUsers'    => Utilisateur::where('statut', 'actif')->count(),
            'newThisMonth'   => Utilisateur::whereMonth('created_at', now()->month)->count(),
            'entities'       => Entite::count(),
        ];

        // Stats tâches pour l'utilisateur connecté
        $userId = auth()->id();
        $tachesStats = [
            'total' => Tache::where('assigne_a', $userId)->orWhere('createur_id', $userId)->count(),
            'enCours' => Tache::whereIn('statut', ['en_cours', 'en_attente'])->where('assigne_a', $userId)->count(),
            'terminees' => Tache::where('statut', 'terminee')->where('assigne_a', $userId)->count(),
            'enRetard' => Tache::where('date_echeance', '<', now())->where('statut', '!=', 'terminee')->where('assigne_a', $userId)->count(),
        ];

        $mesTachesRecentes = Tache::with('assigne')
            ->where('assigne_a', $userId)
            ->orWhere('createur_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        // === NOUVEAU : Actualités et événements récents ===
        $recentAnnonces = Annonce::latest()->take(3)->get();
        $upcomingEvents = Evenement::where('date_debut', '>=', now())
            ->orderBy('date_debut')
            ->take(3)
            ->get();

        return Inertia::render('SuperAdmin/Dashboard', [
            'stats' => $stats,
            'tachesStats' => $tachesStats,
            'mesTachesRecentes' => $mesTachesRecentes,
            'recentAnnonces' => $recentAnnonces,    // ← Nouveau
            'upcomingEvents' => $upcomingEvents,    // ← Nouveau
        ]);
    }
}