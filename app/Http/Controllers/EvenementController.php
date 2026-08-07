<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Utilisateur;
use App\Events\NouvelleNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class EvenementController extends Controller
{
    /**
     * Liste des événements visibles (utilise le scope visible du modèle)
     */
    public function index()
    {
        $evenements = Evenement::visible()
            ->with('organisateur')
            ->get()
            ->map(function ($event) {
                return [
                    'id'           => $event->id,
                    'title'        => $event->titre,
                    'start'        => $event->date_debut,
                    'end'          => $event->date_fin,
                    'allDay'       => $event->duree_minutes >= 1440 || $event->duree_minutes === null,
                    'backgroundColor' => $event->couleur ?? $this->getColorByType($event->type_evenement),
                    'borderColor'     => $event->couleur ?? $this->getColorByType($event->type_evenement),
                    'extendedProps'   => [
                        'description'     => $event->description,
                        'lieu'            => $event->lieu,
                        'lien_virtuel'    => $event->lien_virtuel,
                        'type_lieu'       => $event->type_lieu,
                        'organisateur'    => $event->organisateur?->prenom . ' ' . $event->organisateur?->nom ?? 'Inconnu',
                        'type_evenement'  => $event->type_evenement,
                        'categorie'       => $event->categorie,
                        'est_epingle'     => $event->est_epingle,
                        'visibilite'      => $event->visibilite,
                    ],
                ];
            });

        return Inertia::render('Agenda/Index', [
            'evenements' => $evenements,
        ]);
    }

    /**
     * Formulaire de création d'événement
     */
    public function create()
    {
        // Super-admin peut choisir visibilité globale
        $visibiliteOptions = auth()->user()->hasRole('super_admin')
            ? [
                'entite'     => 'Entité actuelle',
                'global'     => 'Tous les utilisateurs',
                'roles'      => 'Par rôles',
                'groupes'    => 'Par groupes',
                'directions' => 'Par directions',
              ]
            : ['entite' => 'Entité actuelle'];

        return Inertia::render('Agenda/Create', [
            'visibiliteOptions' => $visibiliteOptions,
        ]);
    }

    /**
     * Enregistrement d'un nouvel événement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'date_debut'         => 'required|date',
            'date_fin'           => 'nullable|date|after_or_equal:date_debut',
            'fuseau_horaire'     => 'nullable|string',
            'duree_minutes'      => 'nullable|integer|min:0',
            'type_evenement'     => 'required|in:reunion,formation,evenement_social,rendez_vous,autre',
            'categorie'          => 'nullable|string',
            'couleur'            => 'nullable|string',
            'lieu'               => 'nullable|string',
            'lien_virtuel'       => 'nullable|url',
            'type_lieu'          => 'nullable|in:presentiel,virtuel,hybride',
            'organisateur_id'    => 'nullable|exists:utilisateurs,id',
            'entite_id'          => 'nullable|exists:entites,id',
            'direction_id'       => 'nullable|exists:directions,id',
            'capacite_max'       => 'nullable|integer|min:0',
            'inscription_requise'=> 'boolean',
            'est_recurrent'      => 'boolean',
            'recurrence_pattern' => 'nullable|array',
            'date_fin_recurrence'=> 'nullable|date|after:date_debut',
            'statut'             => 'nullable|string',
            // Nouveaux champs visibilité et priorisation
            'visibilite'         => 'required|in:entite,global,roles,groupes,directions,extranet',
            'roles_cibles'       => 'nullable|array',
            'roles_cibles.*'     => 'exists:roles,nom',
            'groupes_cibles'     => 'nullable|array',
            'directions_cibles'  => 'nullable|array',
            'est_epingle'        => 'boolean',
            'date_epingle_jusqua'=> 'nullable|date|after:today',
        ]);

        // Protection : seul super-admin (ou rôle autorisé) peut publier en global
        if ($request->visibilite === 'global' && !Gate::allows('manage-evenements-global')) {
            abort(403, "Vous n'avez pas les droits pour créer un événement visible par tous.");
        }

        // Données à enregistrer
        $data = $validated;
        $data['organisateur_id'] = auth()->id();
        $data['entite_id'] = auth()->user()->entite_id;
        $data['nombre_participants'] = 0;
        $data['nombre_inscrits'] = 0;

        // Champs booléens
        $data['inscription_requise'] = $request->boolean('inscription_requise', false);
        $data['est_recurrent']       = $request->boolean('est_recurrent', false);
        $data['est_epingle']         = $request->boolean('est_epingle', false);

        $evenement = Evenement::create($data);

        // ======= NOTIFICATIONS =======

        // Déterminer les destinataires selon la visibilité
        $cibles = $this->getCibles($evenement);

        // Ajouter les Super Admins
        $superAdmins = Utilisateur::whereHas('roles', function ($q) {
            $q->where('nom', 'super_admin');
        })->pluck('id')->toArray();

        $tousDestinataires = array_unique(array_merge($cibles, $superAdmins));

        // Créer la notification
        $notification = (object) [
            'id' => uniqid(),
            'data' => [
                'message' => "Nouvel événement : {$evenement->titre}",
                'evenement_id' => $evenement->id,
            ],
        ];

        // Diffuser à chaque destinataire (sauf l'expéditeur)
        foreach ($tousDestinataires as $destId) {
            if ($destId != auth()->id()) {
                broadcast(new NouvelleNotification($notification, $destId));
            }
        }

        return redirect('/agenda')->with('success', 'Événement créé avec succès !');
    }

    /**
     * Détermine les IDs des utilisateurs ciblés par l'événement
     */
    private function getCibles($evenement)
    {
        $cibles = [];

        if ($evenement->visibilite === 'global') {
            $cibles = Utilisateur::pluck('id')->toArray();
        } elseif ($evenement->visibilite === 'entite') {
            $cibles = Utilisateur::where('entite_id', $evenement->entite_id)->pluck('id')->toArray();
        } elseif ($evenement->visibilite === 'directions') {
            $cibles = Utilisateur::whereIn('direction_id', $evenement->directions_cibles ?? [])->pluck('id')->toArray();
        } elseif ($evenement->visibilite === 'roles') {
            $cibles = Utilisateur::whereHas('roles', function ($q) use ($evenement) {
                $q->whereIn('nom', $evenement->roles_cibles ?? []);
            })->pluck('id')->toArray();
        } elseif ($evenement->visibilite === 'groupes') {
            $cibles = Utilisateur::whereIn('id', $evenement->groupes_cibles ?? [])->pluck('id')->toArray();
        }

        return $cibles;
    }

    /**
     * Palette de couleurs par type (inchangée)
     */
    private function getColorByType($type)
    {
        return match ($type) {
            'reunion'          => '#3b82f6',
            'formation'        => '#10b981',
            'evenement_social' => '#f59e0b',
            'rendez_vous'      => '#8b5cf6',
            'autre'            => '#6b7280',
            default            => '#6366f1',
        };
    }

    // À ajouter selon besoin :
    public function show($id)
    {
        $evenement = Evenement::with('organisateur')->findOrFail($id);
        
        if (!$this->canViewEvent($evenement)) {
            abort(403, "Vous n'avez pas accès à cet événement.");
        }

        return Inertia::render('Agenda/Show', [
            'evenement' => $evenement,
        ]);
    }

    public function edit($id)
    {
        $evenement = Evenement::findOrFail($id);

        if ($evenement->organisateur_id !== auth()->id() && !auth()->user()->hasRole('super_admin')) {
             abort(403, "Vous ne pouvez pas modifier cet événement.");
        }

        $visibiliteOptions = auth()->user()->hasRole('super_admin')
            ? [
                'entite'     => 'Entité actuelle',
                'global'     => 'Tous les utilisateurs',
                'roles'      => 'Par rôles',
                'groupes'    => 'Par groupes',
                'directions' => 'Par directions',
              ]
            : ['entite' => 'Entité actuelle'];

        return Inertia::render('Agenda/Edit', [
            'evenement' => $evenement,
            'visibiliteOptions' => $visibiliteOptions,
        ]);
    }

    public function update(Request $request, $id)
    {
        $evenement = Evenement::findOrFail($id);

        if ($evenement->organisateur_id !== auth()->id() && !auth()->user()->hasRole('super_admin')) {
             abort(403, "Vous ne pouvez pas modifier cet événement.");
        }

        $validated = $request->validate([
            'titre'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'date_debut'         => 'required|date',
            'date_fin'           => 'nullable|date|after_or_equal:date_debut',
            'type_evenement'     => 'required|in:reunion,formation,evenement_social,rendez_vous,autre',
            'statut'             => 'nullable|string',
            'lieu'               => 'nullable|string',
            'lien_virtuel'       => 'nullable|url',
        ]);

        $evenement->update($validated);

        return redirect()->route('agenda.index')->with('success', 'Événement mis à jour.');
    }

    public function destroy($id)
    {
        $evenement = Evenement::findOrFail($id);

        if ($evenement->organisateur_id !== auth()->id() && !auth()->user()->hasRole('super_admin')) {
             abort(403, "Vous ne pouvez pas supprimer cet événement.");
        }

        $evenement->delete();

        return redirect()->route('agenda.index')->with('success', 'Événement supprimé.');
    }

    private function canViewEvent($event)
    {
        return true;
    }
}