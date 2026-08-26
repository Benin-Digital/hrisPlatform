<?php

namespace App\Http\Middleware;

use App\Models\Tache;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Inertia\Inertia;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            // === FLASH MESSAGES ===
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],

            // === AUTHENTIFICATION ===
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'prenom' => $user->prenom,
                    'nom' => $user->nom,
                    'email' => $user->email,
                    'entite_id' => $user->entite_id,
                    'entite' => $user->entite ? [
                        'id' => $user->entite->id,
                        'nom' => $user->entite->nom,
                    ] : null,
                    'roles' => $user->roles->map(fn($role) => [
                        'id' => $role->id,
                        'nom' => $role->nom,
                        'nom_affichage' => $role->nom_affichage,
                    ]),
                    'mainRole' => $user->mainRole() ? [
                        'id' => $user->mainRole()->id,
                        'nom' => $user->mainRole()->nom,
                        'nom_affichage' => $user->mainRole()->nom_affichage,
                    ] : null,
                ] : null,
            ],

            // ✅ Tâches personnelles (chargées uniquement si demandées)
            'tachesPersonnelles' => Inertia::lazy(fn () => $user ? $this->getTaskData(
                Tache::where('assigne_a', $user->id)->orWhere('createur_id', $user->id)
            ) : null),

            // ✅ Tâches de l'entité (chargées uniquement si demandées)
            'tachesEntite' => Inertia::lazy(function () use ($user) {
                if (!$user) return null;

                // Super Admin voit tout
                if ($user->hasRole('super_admin')) {
                    return $this->getTaskData(Tache::query());
                }

                // Autres : tâches de l'entité
                if ($user->entite_id) {
                    return $this->getTaskData(Tache::where('entite_id', $user->entite_id));
                }

                return null;
            }),

            // ✅ Compatibilité temporaire (Dashboard)
            'tachesDashboard' => Inertia::lazy(fn () => $user ? $this->getTaskData(
                Tache::where('assigne_a', $user->id)->orWhere('createur_id', $user->id)
            ) : null),
        ]);
    }

    /**
     * Récupère les statistiques et les tâches récentes.
     */
    private function getTaskData($query)
    {
        // Statistiques
        $stats = [
            'total' => (clone $query)->count(),
            'enCours' => (clone $query)->whereIn('statut', ['en_cours', 'en_attente'])->count(),
            'terminees' => (clone $query)->where('statut', 'terminee')->count(),
            'enRetard' => (clone $query)->where('date_echeance', '<', now())->where('statut', '!=', 'terminee')->count(),
        ];

        // Tâches récentes (5 dernières)
        $recents = (clone $query)
            ->with(['assigne:id,prenom,nom', 'createur:id,prenom,nom'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($tache) {
                $tache->createur_prenom_nom = $tache->createur ? $tache->createur->prenom . ' ' . $tache->createur->nom : 'Inconnu';
                $tache->assigne_prenom_nom = $tache->assigne ? $tache->assigne->prenom . ' ' . $tache->assigne->nom : 'Non assignée';
                return $tache;
            });

        return [
            'stats' => $stats,
            'recents' => $recents,
        ];
    }
}