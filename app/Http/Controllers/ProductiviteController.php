<?php

namespace App\Http\Controllers;

use App\Models\Tache;
use App\Models\Utilisateur;
use App\Models\Entite;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductiviteController extends Controller
{
    /**
     * Vue principale - Statistiques globales
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $entiteId = $request->current_entite_id; // Injecté par middleware si applicable

        // Requête de base avec scope entité
        $query = Tache::query();
        
        $isManagerOrHigher = $user->hasAnyRole(['super_admin', 'admin_entite', 'responsable_rh', 'manager']);

        if (!$isManagerOrHigher) {
            // Collaborateur simple : voit uniquement ses tâches
            $query->where('assigne_a', $user->id);
        } elseif (!$user->hasRole('super_admin') && $user->entite_id) {
            // Manager/RH/Admin Entité : voit tout l'entité
            $query->where('entite_id', $user->entite_id);
        }

        // Statistiques globales
        $totalTaches = $query->count();
        $tachesTerminees = (clone $query)->where('statut', 'terminee')->count();
        $tachesEnCours = (clone $query)->where('statut', 'en_cours')->count();
        $tachesEnAttente = (clone $query)->where('statut', 'en_attente')->count();
        $tachesAnnulees = (clone $query)->where('statut', 'annulee')->count();
        
        // Taux de complétion
        $tauxCompletion = $totalTaches > 0 ? round(($tachesTerminees / $totalTaches) * 100, 2) : 0;

        // Tâches en retard
        $tachesEnRetard = (clone $query)
            ->where('date_echeance', '<', now())
            ->whereNotIn('statut', ['terminee', 'annulee'])
            ->count();

        // Progression moyenne
        $progressionMoyenne = round((clone $query)->avg('progression_pourcentage') ?? 0, 2);

        // Répartition par priorité
        $repartitionPriorite = (clone $query)
            ->select('priorite', DB::raw('count(*) as total'))
            ->groupBy('priorite')
            ->get()
            ->pluck('total', 'priorite');

        // Répartition par statut (pour graphique)
        $repartitionStatut = [
            'en_attente' => $tachesEnAttente,
            'en_cours' => $tachesEnCours,
            'terminee' => $tachesTerminees,
            'annulee' => $tachesAnnulees,
        ];

        // Top 5 utilisateurs les plus productifs
        $topUtilisateurs = Utilisateur::select('utilisateurs.id', 'utilisateurs.prenom', 'utilisateurs.nom')
            ->join('taches', 'taches.assigne_a', '=', 'utilisateurs.id')
            ->when(!$user->hasRole('super_admin') && $user->entite_id, function ($q) use ($user) {
                $q->where('taches.entite_id', $user->entite_id);
            })
            ->where('taches.statut', 'terminee')
            ->groupBy('utilisateurs.id', 'utilisateurs.prenom', 'utilisateurs.nom')
            ->orderByRaw('COUNT(taches.id) DESC')
            ->limit(5)
            ->get()
            ->map(function ($u) {
                $u->total_terminees = Tache::where('assigne_a', $u->id)
                    ->where('statut', 'terminee')
                    ->count();
                return $u;
            });

        // Évolution sur les 7 derniers jours
        $evolutionSemaine = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $evolutionSemaine[] = [
                'date' => Carbon::parse($date)->format('d/m'),
                'terminees' => (clone $query)
                    ->where('statut', 'terminee')
                    ->whereDate('updated_at', $date)
                    ->count(),
                'creees' => (clone $query)
                    ->whereDate('created_at', $date)
                    ->count(),
            ];
        }

        // Tâches par entité (si super admin)
        $tachesParEntite = [];
        if ($user->hasRole('super_admin')) {
            $tachesParEntite = Entite::select('entites.id', 'entites.nom')
                ->leftJoin('taches', 'taches.entite_id', '=', 'entites.id')
                ->groupBy('entites.id', 'entites.nom')
                ->get()
                ->map(function ($entite) {
                    return [
                        'nom' => $entite->nom,
                        'total' => Tache::where('entite_id', $entite->id)->count(),
                        'terminees' => Tache::where('entite_id', $entite->id)
                            ->where('statut', 'terminee')
                            ->count(),
                    ];
                });
        }

        return Inertia::render('Productivite/Index', [
            'stats' => [
                'total' => $totalTaches,
                'terminees' => $tachesTerminees,
                'en_cours' => $tachesEnCours,
                'en_attente' => $tachesEnAttente,
                'annulees' => $tachesAnnulees,
                'taux_completion' => $tauxCompletion,
                'en_retard' => $tachesEnRetard,
                'progression_moyenne' => $progressionMoyenne,
            ],
            'repartitionPriorite' => $repartitionPriorite,
            'repartitionStatut' => $repartitionStatut,
            'topUtilisateurs' => $topUtilisateurs,
            'evolutionSemaine' => $evolutionSemaine,
            'tachesParEntite' => $tachesParEntite,
            'isManager' => $isManagerOrHigher,
        ]);
    }

    /**
     * Analyse par utilisateur
     */
    public function parUtilisateur(Request $request, $id = null)
    {
        $user = Auth::user();
        $targetUserId = $id ?? $user->id;

        // Vérification des permissions
        $targetUser = Utilisateur::findOrFail($targetUserId);
        
        $isManagerOrHigher = $user->hasAnyRole(['super_admin', 'admin_entite', 'responsable_rh', 'manager']);

        // 1. Restriction Collaborateur simple -> uniquement lui-même
        if (!$isManagerOrHigher && $user->id !== $targetUserId) {
            abort(403, 'Accès non autorisé aux statistiques d\'un autre utilisateur.');
        }

        // 2. Restriction Manager/RH -> uniquement son entité (sauf super_admin)
        if ($isManagerOrHigher && !$user->hasRole('super_admin') && 
            $user->entite_id !== $targetUser->entite_id) {
            abort(403, 'Accès non autorisé (autre entité).');
        }

        // Statistiques de l'utilisateur
        $tachesAssignees = Tache::where('assigne_a', $targetUserId)->count();
        $tachesCreees = Tache::where('createur_id', $targetUserId)->count();
        $tachesTerminees = Tache::where('assigne_a', $targetUserId)
            ->where('statut', 'terminee')
            ->count();
        $tachesEnCours = Tache::where('assigne_a', $targetUserId)
            ->where('statut', 'en_cours')
            ->count();
        
        $tauxCompletion = $tachesAssignees > 0 
            ? round(($tachesTerminees / $tachesAssignees) * 100, 2) 
            : 0;

        // Temps moyen de réalisation (si date_fin_reelle existe)
        $tempsMoyen = Tache::where('assigne_a', $targetUserId)
            ->where('statut', 'terminee')
            ->whereNotNull('date_debut')
            ->whereNotNull('date_fin_reelle')
            ->get()
            ->avg(function ($tache) {
                return Carbon::parse($tache->date_debut)
                    ->diffInDays(Carbon::parse($tache->date_fin_reelle));
            });

        // Tâches récentes
        $tachesRecentes = Tache::where('assigne_a', $targetUserId)
            ->with(['createur', 'entite'])
            ->latest()
            ->limit(10)
            ->get();

        // Liste des utilisateurs (pour sélection)
        $utilisateurs = Utilisateur::select('id', 'prenom', 'nom', 'entite_id')
            ->when(!$user->hasRole('super_admin') && $user->entite_id, function ($q) use ($user) {
                $q->where('entite_id', $user->entite_id);
            })
            ->orderBy('prenom')
            ->get();

        return Inertia::render('Productivite/ParUtilisateur', [
            'utilisateurCible' => $targetUser,
            'stats' => [
                'assignees' => $tachesAssignees,
                'creees' => $tachesCreees,
                'terminees' => $tachesTerminees,
                'en_cours' => $tachesEnCours,
                'taux_completion' => $tauxCompletion,
                'temps_moyen_jours' => round($tempsMoyen ?? 0, 1),
            ],
            'tachesRecentes' => $tachesRecentes,
            'utilisateurs' => $utilisateurs,
        ]);
    }

    /**
     * Analyse par entité
     */
    public function parEntite(Request $request, $id = null)
    {
        $user = Auth::user();

        // Restriction : Seuls les managers/admins peuvent voir l'analyse par entité
        if (!$user->hasAnyRole(['super_admin', 'admin_entite', 'responsable_rh', 'manager'])) {
            abort(403, 'Accès non autorisé (Réservé aux managers).');
        }
        
        // Déterminer l'entité cible
        if ($user->hasRole('super_admin')) {
            $targetEntiteId = $id ?? Entite::first()->id;
        } else {
            $targetEntiteId = $user->entite_id;
            if ($id && $id != $user->entite_id) {
                abort(403, 'Accès non autorisé.');
            }
        }

        $entite = Entite::findOrFail($targetEntiteId);

        // Statistiques de l'entité
        $totalTaches = Tache::where('entite_id', $targetEntiteId)->count();
        $terminees = Tache::where('entite_id', $targetEntiteId)
            ->where('statut', 'terminee')
            ->count();
        $enCours = Tache::where('entite_id', $targetEntiteId)
            ->where('statut', 'en_cours')
            ->count();

        $tauxCompletion = $totalTaches > 0 
            ? round(($terminees / $totalTaches) * 100, 2) 
            : 0;

        // Utilisateurs de l'entité avec leurs stats
        $utilisateursStats = Utilisateur::where('entite_id', $targetEntiteId)
            ->select('id', 'prenom', 'nom')
            ->get()
            ->map(function ($u) {
                $assignees = Tache::where('assigne_a', $u->id)->count();
                $terminees = Tache::where('assigne_a', $u->id)
                    ->where('statut', 'terminee')
                    ->count();
                
                return [
                    'id' => $u->id,
                    'nom_complet' => "{$u->prenom} {$u->nom}",
                    'taches_assignees' => $assignees,
                    'taches_terminees' => $terminees,
                    'taux_completion' => $assignees > 0 
                        ? round(($terminees / $assignees) * 100, 2) 
                        : 0,
                ];
            })
            ->sortByDesc('taches_terminees')
            ->values();

        // Liste des entités (pour sélection si super admin)
        $entites = [];
        if ($user->hasRole('super_admin')) {
            $entites = Entite::select('id', 'nom')->orderBy('nom')->get();
        }

        return Inertia::render('Productivite/ParEntite', [
            'entiteCible' => $entite,
            'stats' => [
                'total' => $totalTaches,
                'terminees' => $terminees,
                'en_cours' => $enCours,
                'taux_completion' => $tauxCompletion,
            ],
            'utilisateursStats' => $utilisateursStats,
            'entites' => $entites,
        ]);
    }

    /**
     * Rapport sur une période
     */
    public function rapportPeriode(Request $request)
    {
        $user = Auth::user();
        
        $dateDebut = $request->input('date_debut', Carbon::now()->subMonth()->format('Y-m-d'));
        $dateFin = $request->input('date_fin', Carbon::now()->format('Y-m-d'));

        $query = Tache::whereBetween('created_at', [$dateDebut, $dateFin]);

        $isManagerOrHigher = $user->hasAnyRole(['super_admin', 'admin_entite', 'responsable_rh', 'manager']);

        if (!$isManagerOrHigher) {
            $query->where('assigne_a', $user->id);
        } elseif (!$user->hasRole('super_admin') && $user->entite_id) {
            $query->where('entite_id', $user->entite_id);
        }

        $stats = [
            'total_creees' => $query->count(),
            'terminees' => (clone $query)->where('statut', 'terminee')->count(),
            'en_cours' => (clone $query)->where('statut', 'en_cours')->count(),
        ];

        return response()->json([
            'periode' => [
                'debut' => $dateDebut,
                'fin' => $dateFin,
            ],
            'stats' => $stats,
        ]);
    }
}
