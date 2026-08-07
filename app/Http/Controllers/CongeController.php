<?php
namespace App\Http\Controllers;
use App\Models\Conge;
use App\Models\Utilisateur;
use App\Events\NouvelleNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\SoldeConge;

class CongeController extends Controller
{
    /**
     * Liste des congés (selon le rôle)
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            $conges = Conge::with(['utilisateur', 'validateur'])
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($user->hasRole('manager') || $user->hasRole('responsable_rh')) {
            // Manager/RH voit les congés de son entité
            $conges = Conge::whereHas('utilisateur', function ($q) use ($user) {
                $q->where('entite_id', $user->entite_id);
            })->with(['utilisateur', 'validateur'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Collaborateur voit ses propres congés
            $conges = Conge::where('utilisateur_id', $user->id)
                ->with(['utilisateur', 'validateur'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $stats = [
            'total'        => $conges->count(),
            'en_attente'   => $conges->where('statut', 'en_attente')->count(),
            'valides'      => $conges->where('statut', 'valide')->count(),
            'rejetes'      => $conges->where('statut', 'rejete')->count(),
        ];

        return Inertia::render('Conges/Index', [
            'conges'      => $conges,
            'stats'       => $stats,
            'canValider'  => $user->hasAnyRole(['super_admin', 'manager', 'responsable_rh']),
            'canCreer'    => true,
        ]);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $user = Auth::user();
        $soldeAnnuel = SoldeConge::where('utilisateur_id', $user->id)
            ->where('type_conge', 'annuel')
            ->where('annee', date('Y'))
            ->first();

        return Inertia::render('Conges/Create', [
            'soldeAnnuel' => $soldeAnnuel?->solde_restant ?? 0,
        ]);
    }

    /**
     * Enregistrer une demande de congé
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type_conge' => 'required|in:annuel,maladie,sans_solde,formation,autre',
            'date_debut' => 'required|date|after_or_equal:today',
            'date_fin'   => 'required|date|after_or_equal:date_debut',
            'motif'      => 'nullable|string|max:500',
        ]);

        // Calcul de la durée en jours ouvrables (hors samedi/dimanche).
        // Le champ s'appelle duree_ouvrable : un calcul en jours
        // calendaires gonflerait artificiellement la durée pour toute
        // période chevauchant un week-end.
        $debut = new \DateTime($validated['date_debut']);
        $fin   = new \DateTime($validated['date_fin']);
        $duree = 0;
        $curseur = clone $debut;
        while ($curseur <= $fin) {
            $jourSemaine = (int) $curseur->format('N'); // 1 = lundi ... 7 = dimanche
            if ($jourSemaine < 6) {
                $duree++;
            }
            $curseur->modify('+1 day');
        }

        // Seul le congé annuel est plafonné par un solde géré en base.
        // Les autres types (maladie, sans solde, formation, autre) ne
        // sont pas soumis à cette vérification : un congé maladie ne
        // doit pas être refusé faute de "solde annuel" disponible.
        if ($validated['type_conge'] === 'annuel') {
            $solde = SoldeConge::where('utilisateur_id', Auth::id())
                ->where('type_conge', 'annuel')
                ->where('annee', date('Y'))
                ->first();

            if (!$solde || !$solde->aAssezDeJours($duree)) {
                return back()->with('error', 'Solde de congé annuel insuffisant.');
            }
        }

        // Création du congé
        $conge = Conge::create([
            'utilisateur_id' => Auth::id(),
            'type_conge'     => $validated['type_conge'],
            'date_debut'     => $validated['date_debut'],
            'date_fin'       => $validated['date_fin'],
            'duree_ouvrable' => $duree,
            'motif'          => $validated['motif'],
            'statut'         => 'en_attente',
        ]);

        // Notifier les managers/RH
        $this->notifierManagers($conge);

        return redirect()->route('conges.index')
            ->with('success', 'Demande de congé envoyée avec succès.');
    }

    /**
     * Afficher une demande
     */
    public function show($id)
    {
        $conge = Conge::with(['utilisateur', 'validateur'])->findOrFail($id);
        $user = Auth::user();

        if (
            !$user->hasRole('super_admin') &&
            !$user->hasRole('manager') &&
            !$user->hasRole('responsable_rh') &&
            $conge->utilisateur_id !== $user->id
        ) {
            abort(403, "Vous n'avez pas accès à cette demande.");
        }

        return Inertia::render('Conges/Show', [
            'conge'      => $conge,
            'canValider' => $user->hasAnyRole(['super_admin', 'manager', 'responsable_rh']),
        ]);
    }

    /**
     * Valider ou rejeter une demande
     */
    public function valider(Request $request, $id)
    {
        $conge = Conge::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['super_admin', 'manager', 'responsable_rh'])) {
            abort(403, "Vous n'avez pas les droits pour valider des congés.");
        }

        if (!$user->hasRole('super_admin') && $conge->utilisateur->entite_id !== $user->entite_id) {
            abort(403, "Vous ne pouvez valider que les congés de votre entité.");
        }

        $validated = $request->validate([
            'statut'                => 'required|in:valide,rejete',
            'commentaire_validation' => 'nullable|string|max:500',
        ]);

        $commentaire = $request->input('commentaire_validation', null);

        $conge->update([
            'statut'                => $validated['statut'],
            'valide_par'            => $user->id,
            'date_validation'       => now(),
            'commentaire_validation' => $commentaire,
        ]);

        // Recharger le modèle pour avoir le commentaire à jour
        $conge->refresh();

        // Notifier le demandeur avec le commentaire
        $this->notifierDemandeur($conge, $commentaire);
        // Décrémenter le solde si validé. Seul le congé annuel est
        // réellement plafonné et suivi via SoldeConge (voir store()).
        // Le type "formation" est laissé ici par anticipation d'un futur
        // suivi de solde dédié, mais n'a aujourd'hui aucun solde en base :
        // le decrementerSolde() ci-dessous restera donc sans effet pour
        // "formation" tant qu'aucune ligne SoldeConge n'existe pour ce type.
        if ($validated['statut'] === 'valide') {
            $typesAvecSuiviSolde = ['annuel', 'formation'];
            if (in_array($conge->type_conge, $typesAvecSuiviSolde)) {
                $solde = SoldeConge::where('utilisateur_id', $conge->utilisateur_id)
                    ->where('type_conge', $conge->type_conge)
                    ->where('annee', $conge->date_debut->year)
                    ->first();

                if ($solde) {
                    $solde->decrementerSolde($conge->duree_ouvrable);
                }
            }
        }



        return redirect()->route('conges.index')
            ->with('success', 'Demande de congé ' . ($validated['statut'] === 'valide' ? 'validée' : 'rejetée') . ' avec succès.');
    }

    /**
     * Annuler une demande (seulement si en attente)
     */
    public function annuler($id)
    {
        $conge = Conge::findOrFail($id);
        $user = Auth::user();

        if ($conge->utilisateur_id !== $user->id && !$user->hasRole('super_admin')) {
            abort(403, "Vous ne pouvez pas annuler cette demande.");
        }

        if ($conge->statut !== 'en_attente') {
            return back()->with('error', 'Seules les demandes en attente peuvent être annulées.');
        }

        $conge->update(['statut' => 'annule']);

        return redirect()->route('conges.index')
            ->with('success', 'Demande de congé annulée.');
    }

    // ==================== NOTIFICATIONS ====================

    private function notifierManagers($conge)
    {
        $managers = Utilisateur::where('entite_id', $conge->utilisateur->entite_id)
            ->whereHas('roles', function ($q) {
                $q->whereIn('nom', ['manager', 'responsable_rh', 'super_admin']);
            })
            ->pluck('id')
            ->toArray();

        if (empty($managers)) return;

        $notification = (object) [
            'id' => uniqid(),
            'data' => [
                'message' => "Nouvelle demande de congé de {$conge->utilisateur->prenom} {$conge->utilisateur->nom} du {$conge->date_debut} au {$conge->date_fin}.",
                'conge_id' => $conge->id,
            ],
        ];

        foreach ($managers as $managerId) {
            broadcast(new NouvelleNotification($notification, $managerId));
        }
    }

    private function notifierDemandeur($conge, $commentaire = null)
    {
        $statusLabel = $conge->statut === 'valide' ? '✅ validée' : '❌ rejetée';
        $message = "Votre demande de congé (du {$conge->date_debut} au {$conge->date_fin}) a été {$statusLabel}.";
        if ($commentaire) {
            $message .= " Commentaire : {$commentaire}";
        }

        $notification = (object) [
            'id' => uniqid(),
            'data' => [
                'message' => $message,
                'conge_id' => $conge->id,
            ],
        ];
        broadcast(new NouvelleNotification($notification, $conge->utilisateur_id));
    }
}