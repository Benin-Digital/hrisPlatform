<?php

namespace App\Http\Middleware;

use App\Models\Tache;
use Illuminate\Http\Request;
use Inertia\Middleware;

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

        $tachesPersonnelles = null;
        $tachesEntite = null;

        if ($user) {
            $userId = $user->id;
            $entiteId = $user->entite_id;

            // === Tâches personnelles ===
            $baseQuery = Tache::where('assigne_a', $userId)->orWhere('createur_id', $userId);
            $tachesPersonnelles = $this->getTaskData($baseQuery);

            // === Tâches de l'entité ===
            if ($entiteId) {
                $entiteQuery = Tache::where('entite_id', $entiteId);
                $tachesEntite = $this->getTaskData($entiteQuery);
            }

            // === Tâches Globales (Super Admin) ===
            if ($user->hasRole('super_admin')) {
                $tachesEntite = $this->getTaskData(Tache::query());
            }
        }

        return array_merge(parent::share($request), [
            // === FLASH MESSAGES ===
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],

            // === AUTHENTIFICATION : INDISPENSABLE ===
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

            // Tâches
            'tachesPersonnelles' => $tachesPersonnelles,
            'tachesEntite' => $tachesEntite,
            'tachesDashboard' => $tachesPersonnelles, // Compatibilité temporaire
        ]);
    }

    private function getTaskData($query)
    {
        return [
            'stats' => [
                'total' => (clone $query)->count(),
                'enCours' => (clone $query)->whereIn('statut', ['en_cours', 'en_attente'])->count(),
                'terminees' => (clone $query)->where('statut', 'terminee')->count(),
                'enRetard' => (clone $query)->where('date_echeance', '<', now())->where('statut', '!=', 'terminee')->count(),
            ],
            'recents' => (clone $query)->with(['assigne:id,prenom,nom', 'createur:id,prenom,nom'])
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($tache) {
                    $tache->createur_prenom_nom = $tache->createur ? $tache->createur->prenom . ' ' . $tache->createur->nom : 'Inconnu';
                    $tache->assigne_prenom_nom = $tache->assigne ? $tache->assigne->prenom . ' ' . $tache->assigne->nom : 'Non assignée';
                    return $tache;
                }),
        ];
    }
}