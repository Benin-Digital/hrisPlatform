<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use App\Models\Utilisateur;
use App\Events\NouvelleNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class AnnonceController extends Controller
{
    /**
     * Liste des annonces visibles (scope visible + tri)
     */
    public function index()
    {
        $user = auth()->user();
        $epinglees = Annonce::visible()
            ->with('createur')
            ->where('est_epingle', true)
            ->when(!$user->hasRole('super_admin'), function ($query) use ($user) {
                $query->where('entite_id', $user->entite_id);
            })
            ->orderBy('date_publication', 'desc')
            ->get();

        $annonces = Annonce::visible()
            ->with('createur')
            ->when(!$user->hasRole('super_admin'), function ($query) use ($user) {
                $query->where('entite_id', $user->entite_id);
            })
            ->orderBy('est_epingle', 'desc')
            ->orderBy('date_publication', 'desc')
            ->paginate(12);

        return Inertia::render('Actualites/Index', [
            'annonces'  => $annonces,
            'epinglees' => $epinglees,
        ]);
    }

    /**
     * Affichage d'une annonce (détail)
     */
    public function show($id)
    {
        $annonce = Annonce::visible()
            ->with('createur')
            ->findOrFail($id);

        return Inertia::render('Actualites/Show', [
            'annonce' => $annonce,
        ]);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $visibiliteOptions = auth()->user()->hasRole('super_admin')
            ? [
                'entite'     => 'Entité actuelle seulement',
                'global'     => 'Visible pour tous (global)',
                'roles'      => 'Par rôles spécifiques',
                'groupes'    => 'Par groupes spécifiques',
                'directions' => 'Par directions spécifiques',
                'extranet'   => 'Uniquement Extranet (Partenaires)',
              ]
            : ['entite' => 'Entité actuelle seulement'];

        $rolesDisponibles      = \App\Models\Role::select('nom', 'nom_affichage')->get();
        $directionsDisponibles = \App\Models\Direction::select('id', 'nom')->get();

        return Inertia::render('Actualites/Create', [
            'visibiliteOptions'     => $visibiliteOptions,
            'rolesDisponibles'      => $rolesDisponibles,
            'directionsDisponibles' => $directionsDisponibles,
        ]);
    }

    /**
     * Enregistrement d'une nouvelle annonce
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre'              => 'required|string|max:255',
            'contenu'            => 'required|string',
            'image'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type_annonce'       => 'required|in:information,urgent,evenement,rh,autre',
            'cible_type'         => 'required|in:tous,direction,groupes,utilisateurs',
            'direction_id'       => 'nullable|exists:directions,id',
            'groupes_cibles'     => 'nullable|string',
            'utilisateurs_cibles'=> 'nullable|string',
            'est_epingle'        => 'boolean',
            'date_epingle_jusqua'=> 'nullable|date|after:today',
            'date_expiration'    => 'nullable|date|after:today',
            'visibilite'         => 'required|in:entite,global,roles,groupes,directions',
            'roles_cibles'       => 'nullable|array',
            'roles_cibles.*'     => 'exists:roles,nom',
            'groupes_cibles'     => 'nullable|array',
            'directions_cibles'  => 'nullable|array',
        ]);

        if ($request->visibilite === 'global' && !Gate::allows('manage-annonces-global')) {
            abort(403, "Vous n'avez pas les droits pour publier une annonce visible par tous les utilisateurs.");
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('annonces', 'public');
        }

        $annonce = Annonce::create([
            'titre'              => $validated['titre'],
            'contenu'            => $validated['contenu'],
            'image'              => $imagePath,
            'type_annonce'       => $validated['type_annonce'],
            'cible_type'         => $validated['cible_type'],
            'auteur_id'          => auth()->id(),
            'entite_id'          => auth()->user()->entite_id ?? 1,
            'direction_id'       => $validated['direction_id'] ?? null,
            'groupes_cibles'     => $validated['groupes_cibles'] ?? null,
            'utilisateurs_cibles'=> $validated['utilisateurs_cibles'] ?? null,
            'est_epingle'        => $request->boolean('est_epingle', false),
            'date_epingle_jusqua'=> $validated['date_epingle_jusqua'] ?? null,
            'date_expiration'    => $validated['date_expiration'] ?? null,
            'visibilite'         => $validated['visibilite'],
            'roles_cibles'       => $request->input('roles_cibles', []),
            'groupes_cibles'     => $request->input('groupes_cibles', []),
            'directions_cibles'  => $request->input('directions_cibles', []),
        ]);

        $cibles = $this->getCibles($annonce);
        $superAdmins = Utilisateur::whereHas('roles', function ($q) {
            $q->where('nom', 'super_admin');
        })->pluck('id')->toArray();

        $tousDestinataires = array_unique(array_merge($cibles, $superAdmins));

        $notification = (object) [
            'id' => uniqid(),
            'data' => [
                'message' => "Nouvelle annonce : {$annonce->titre}",
                'annonce_id' => $annonce->id,
            ],
        ];

        foreach ($tousDestinataires as $destId) {
            if ($destId != auth()->id()) {
                broadcast(new NouvelleNotification($notification, $destId));
            }
        }

        return redirect()->route('actualites.index')
            ->with('success', 'Annonce publiée avec succès !');
    }

    /**
     * Détermine les IDs des utilisateurs ciblés par l'annonce
     */
    private function getCibles($annonce)
    {
        $cibles = [];

        if ($annonce->visibilite === 'global') {
            $cibles = Utilisateur::pluck('id')->toArray();
        } elseif ($annonce->visibilite === 'entite') {
            $cibles = Utilisateur::where('entite_id', $annonce->entite_id)->pluck('id')->toArray();
        } elseif ($annonce->visibilite === 'directions') {
            $cibles = Utilisateur::whereIn('direction_id', $annonce->directions_cibles ?? [])->pluck('id')->toArray();
        } elseif ($annonce->visibilite === 'roles') {
            $cibles = Utilisateur::whereHas('roles', function ($q) use ($annonce) {
                $q->whereIn('nom', $annonce->roles_cibles ?? []);
            })->pluck('id')->toArray();
        } elseif ($annonce->visibilite === 'groupes') {
            $cibles = Utilisateur::whereIn('id', $annonce->groupes_cibles ?? [])->pluck('id')->toArray();
        }

        return $cibles;
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $annonce = Annonce::findOrFail($id);

        // ✅Correction : utilisation de hasAnyRole
        if (auth()->id() !== $annonce->auteur_id && !auth()->user()->hasAnyRole(['super_admin', 'admin_entite', 'responsable_rh'])) {
            abort(403);
        }

        $visibiliteOptions = auth()->user()->hasRole('super_admin')
            ? ['entite' => 'Entité actuelle', 'global' => 'Tous les utilisateurs', 'roles' => 'Par rôles', 'groupes' => 'Par groupes', 'directions' => 'Par directions']
            : ['entite' => 'Entité actuelle'];

        $rolesDisponibles      = \App\Models\Role::select('nom', 'nom_affichage')->get();
        $directionsDisponibles = \App\Models\Direction::select('id', 'nom')->get();

        return Inertia::render('Actualites/Edit', [
            'annonce'               => $annonce,
            'visibiliteOptions'     => $visibiliteOptions,
            'rolesDisponibles'      => $rolesDisponibles,
            'directionsDisponibles' => $directionsDisponibles,
        ]);
    }

    /**
     * Mise à jour d'une annonce
     */
   public function update(Request $request, $id)
{
    $annonce = Annonce::findOrFail($id);

    // Autorisation
    if (auth()->id() !== $annonce->auteur_id && !auth()->user()->hasAnyRole(['super_admin', 'admin_entite', 'responsable_rh'])) {
        abort(403);
    }

    // Récupérer toutes les données (y compris les fichiers)
    $data = $request->all();

    // Décoder les champs JSON (car ils arrivent en tant que chaînes via FormData)
    $jsonFields = ['roles_cibles', 'groupes_cibles', 'directions_cibles'];
    foreach ($jsonFields as $field) {
        if (isset($data[$field]) && is_string($data[$field])) {
            $decoded = json_decode($data[$field], true);
            $data[$field] = is_array($decoded) ? $decoded : [];
        }
    }

    // Valider (utiliser $data car $request->all() contient déjà les champs modifiés)
    $validated = validator($data, [
        'titre'              => 'required|string|max:255',
        'contenu'            => 'required|string',
        'image'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'type_annonce'       => 'required|in:information,urgent,evenement,rh,autre',
        'cible_type'         => 'required|in:tous,direction,groupes,utilisateurs',
        'direction_id'       => 'nullable|exists:directions,id',
        //'groupes_cibles'     => 'nullable|string',
        'utilisateurs_cibles'=> 'nullable|string',
        'est_epingle'        => 'boolean',
        'date_epingle_jusqua'=> 'nullable|date|after:today',
        'date_expiration'    => 'nullable|date|after:today',
        'visibilite'         => 'required|in:entite,global,roles,groupes,directions',
        'roles_cibles'       => 'nullable|array',
        'roles_cibles.*'     => 'exists:roles,nom',
        'groupes_cibles'     => 'nullable|array',
        'directions_cibles'  => 'nullable|array',
    ])->validate();

    // Protection globale
    if ($validated['visibilite'] === 'global' && !Gate::allows('manage-annonces-global')) {
        abort(403);
    }

    // Gestion de l'image
    if ($request->hasFile('image')) {
        if ($annonce->image) {
            Storage::disk('public')->delete($annonce->image);
        }
        $validated['image'] = $request->file('image')->store('annonces', 'public');
    }

    $annonce->update($validated);

    return redirect()->route('actualites.index')
        ->with('success', 'Annonce mise à jour avec succès !');
}

    /**
     * Suppression d'une annonce
     */
    public function destroy($id)
    {
        $annonce = Annonce::findOrFail($id);

        // ✅ Correction : utilisation de hasAnyRole
        if (auth()->id() !== $annonce->auteur_id && !auth()->user()->hasAnyRole(['super_admin', 'admin_entite', 'responsable_rh'])) {
            abort(403);
        }

        if ($annonce->image && Storage::disk('public')->exists($annonce->image)) {
            Storage::disk('public')->delete($annonce->image);
        }

        $annonce->delete();

        return redirect()->route('actualites.index')
            ->with('success', 'Annonce supprimée avec succès.');
    }
}