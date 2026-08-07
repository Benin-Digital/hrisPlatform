<?php

namespace App\Http\Controllers;

use App\Models\Tache;
use App\Models\Annonce;
use App\Models\Evenement;
use App\Models\Document;
use App\Models\Formation;
use App\Models\Utilisateur;
use App\Models\Conge;
use App\Models\OffreEmploi;
use App\Models\InscriptionFormation;
use App\Models\EvaluationFormation;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $role = $user->mainRole()?->nom ?? 'collaborateur';

        $view = match ($role) {
            'super_admin'    => 'SuperAdmin/Dashboard',
            'admin_entite'   => 'AdminEntite/Dashboard',
            'responsable_rh' => 'RH/Dashboard',
            'manager'        => 'Manager/Dashboard',
            'formateur'      => 'Formateur/Dashboard',
            'collaborateur'  => 'Collaborateur/Dashboard',
            'invite'         => 'Extranet/Dashboard',
            default          => 'Collaborateur/Dashboard',
        };

        return Inertia::render($view, $this->getCommonData());
    }

    public function collaborateur()
    {
        $user = Auth::user();

        $taches = Tache::where('assigne_a', $user->id)
            ->whereIn('statut', ['en_cours', 'en_attente'])
            ->orderBy('date_echeance', 'asc')
            ->take(5)
            ->get();

        $docs = Document::where(function($q) use ($user) {
                $q->where('proprietaire_id', $user->id)
                  ->orWhere('entite_id', $user->entite_id)
                  ->orWhereNull('entite_id');
            })
            ->orWhereHas('partages', function($q) use ($user) {
                $q->where(function($sq) use ($user) {
                    $sq->where('partage_avec_type', 'utilisateur')->where('partage_avec_id', $user->id);
                })->orWhere(function($sq) use ($user) {
                    $sq->where('partage_avec_type', 'entite')->where('partage_avec_id', $user->entite_id);
                })->orWhere(function($sq) use ($user) {
                    $sq->where('partage_avec_type', 'direction')->where('partage_avec_id', $user->direction_id);
                })->orWhere('partage_avec_type', 'global')
                  ->orWhere('partage_avec_type', 'extranet');
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $formations = Formation::where('statut', 'publie')
            ->where('entite_id', $user->entite_id)
            ->orderBy('date_debut', 'asc')
            ->take(5)
            ->get();

        return Inertia::render('Collaborateur/Dashboard', array_merge($this->getCommonData(), [
            'tachesPersonnelles' => $taches,
            'documentsRecents'   => $docs,
            'formationsDispo'    => $formations,
        ]));
    }

    public function manager()
    {
        $user = Auth::user();
        $entiteId = $user->entite_id;   
        
        $stats = [
            'tachesEnCours'   => Tache::where('entite_id', $entiteId)->whereIn('statut', ['en_attente','en_cours'])->count(),
            'tachesTerminees' => Tache::where('entite_id', $entiteId)->where('statut', 'terminee')->count(),
            'collaborateurs'  => Utilisateur::where('entite_id', $entiteId)->where('type','interne')->count(),
            'tachesEnRetard'  => Tache::where('entite_id', $entiteId)
                                    ->where('date_echeance', '<', now())
                                    ->whereNotIn('statut', ['terminee', 'annulee'])
                                    ->count(),
        ];

        return Inertia::render('Manager/Dashboard', [
            'stats' => $stats,
        ]);
    }

    public function rh()
    {
        $user = Auth::user();
        $entiteId = $user->entite_id;

        $stats = [
            'totalCollaborateurs' => Utilisateur::where('entite_id', $entiteId)->where('type', 'interne')->count(),
            'congesEnAttente'     => Conge::whereHas('utilisateur', function($q) use ($entiteId) {
                $q->where('entite_id', $entiteId);
            })->where('statut', 'en_attente')->count(),
            'formationsEnCours'   => Formation::where('entite_id', $entiteId)->where('statut', 'publie')->count(),
            'offresActives'       => OffreEmploi::where('is_published', true)->count(),
        ];

        $recentAnnonces = Annonce::where('entite_id', $entiteId)->latest()->take(3)->get();
        $upcomingEvents = Evenement::where('entite_id', $entiteId)->where('date_debut', '>=', now())->orderBy('date_debut')->take(3)->get();
          
        return Inertia::render('RH/Dashboard', [
            'stats' => $stats,
            'recentAnnonces' => $recentAnnonces,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    public function adminEntite()
    {
        $user = Auth::user();
        $entiteId = $user->entite_id;

        $stats = [
            'totalCollaborateurs' => Utilisateur::where('entite_id', $entiteId)->where('type', 'interne')->count(),
            'tachesEnCours'       => Tache::where('entite_id', $entiteId)->whereIn('statut', ['en_attente', 'en_cours'])->count(),
            'documentsTotal'      => Document::where('entite_id', $entiteId)->count(),
            'formationsActives'   => Formation::where('entite_id', $entiteId)->where('statut', 'publie')->count(),
        ];

        return Inertia::render('AdminEntite/Dashboard', array_merge($this->getCommonData(), [
            'stats' => $stats,
        ]));
    }

    public function formateur()
    {
        $user = Auth::user();
        $entiteId = $user->entite_id;

        $stats = [
            'formationsPubliees' => Formation::where('entite_id', $entiteId)->where('statut', 'publie')->count(),
            'inscritsTotal'      => InscriptionFormation::whereHas('formation', function($q) use ($entiteId) {
                $q->where('entite_id', $entiteId);
            })->count(),
            'evaluationsRecues'  => EvaluationFormation::whereHas('formation', function($q) use ($entiteId) {
                $q->where('entite_id', $entiteId);
            })->count(),
            'noteMoyenne'        => EvaluationFormation::whereHas('formation', function($q) use ($entiteId) {
                $q->where('entite_id', $entiteId);
            })->avg('note') ?? 0,
        ];

        return Inertia::render('Formateur/Dashboard', array_merge($this->getCommonData(), [
            'stats' => $stats,
        ]));
    }

    public function extranet()
    {
        return Inertia::render('Extranet/Dashboard', $this->getCommonData());
    }

    /**
     * Fetch common widgets data: News, Events, etc.
     */
    private function getCommonData()
    {
        return [
            'recentAnnonces' => Annonce::visible()
                ->with('createur')
                ->orderBy('est_epingle', 'desc')
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get(),

            'upcomingEvents' => Evenement::visible()
                ->where('date_debut', '>=', now())
                ->orderBy('date_debut', 'asc')
                ->take(3)
                ->get(),
        ];
    }
}