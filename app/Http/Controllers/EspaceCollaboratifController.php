<?php

namespace App\Http\Controllers;

use App\Models\EspaceCollaboratif;
use App\Models\EspaceMembre;
use App\Models\Utilisateur;
use App\Models\Entite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EspaceCollaboratifController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $espaces = EspaceCollaboratif::where(function ($query) use ($user) {
                // Espaces où l'utilisateur est membre
                $query->whereHas('membres', function ($q) use ($user) {
                    $q->where('utilisateur_id', $user->id);
                });

                // Pour les collaborateurs internes, montrer aussi les espaces publics de leur entité
                if (!$user->hasRole('invite')) {
                    $query->orWhere(function ($q) use ($user) {
                        $q->where('est_prive', false)
                          ->where('entite_id', $user->entite_id);
                    });
                }
            })
            ->with(['createur:id,nom,prenom', 'membres'])
            ->withCount('membres')
            ->orderBy('updated_at', 'desc')
            ->get();

        return Inertia::render('Collaboration/Index', [
            'espaces' => $espaces,
        ]);
    }

    public function create()
    {
        if (Auth::user()->hasRole('invite')) {
            abort(403, "Les utilisateurs invités ne peuvent pas créer d'espaces.");
        }
        
        $entites = Entite::select('id', 'nom')->get();
        return Inertia::render('Collaboration/Create', [
            'entites' => $entites,
        ]);
    }

    public function store(Request $request)
    {
        if (Auth::user()->hasRole('invite')) {
            abort(403, "Les utilisateurs invités ne peuvent pas créer d'espaces.");
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'est_prive' => 'required|boolean',
            'entite_id' => 'nullable|exists:entites,id',
        ]);

        $espace = EspaceCollaboratif::create([
            'nom' => $validated['nom'],
            'description' => $validated['description'],
            'est_prive' => $validated['est_prive'],
            'entite_id' => $validated['entite_id'] ?? Auth::user()->entite_id,
            'createur_id' => Auth::id(),
            'statut' => 'actif',
        ]);

        // Le créateur est automatiquement admin de l'espace
        EspaceMembre::create([
            'espace_id' => $espace->id,
            'utilisateur_id' => Auth::id(),
            'role' => 'admin',
        ]);

        return redirect()->route('collaboration.show', $espace->uuid)
            ->with('success', 'Espace collaboratif créé avec succès.');
    }

    public function show($uuid)
    {
        $espace = EspaceCollaboratif::where('uuid', $uuid)
        ->with([
            'createur:id,nom,prenom', 
            'membres:id,nom,prenom,photo_profil,email',
            'documents',
            'taches.assigne:id,nom,prenom'
        ])
        ->firstOrFail();

        // Vérifier si l'utilisateur y a accès
        $isMembre = $espace->membres->contains('id', Auth::id());
        if ($espace->est_prive && !$isMembre && !Auth::user()->hasRole('super_admin')) {
            abort(403, "Vous n'avez pas accès à cet espace privé.");
        }

        // Récupérer les utilisateurs qui ne sont pas encore membres
        $membresIds = $espace->membres->pluck('id');
        $usersToInvite = Utilisateur::whereNotIn('id', $membresIds)
            ->with('entite:id,nom')
            ->select('id', 'nom', 'prenom', 'entite_id')
            ->get();

        return Inertia::render('Collaboration/Show', [
            'espace' => $espace,
            'isMembre' => $isMembre,
            'isAdmin' => $isMembre ? $espace->membres->find(Auth::id())->pivot->role === 'admin' : false,
            'usersToInvite' => $usersToInvite,
        ]);
    }

    public function addMember(Request $request, $uuid)
    {
        $espace = EspaceCollaboratif::where('uuid', $uuid)->firstOrFail();
        
        // Seul un admin de l'espace peut ajouter des membres
        $pivot = EspaceMembre::where('espace_id', $espace->id)
            ->where('utilisateur_id', Auth::id())
            ->first();

        if (!$pivot || ($pivot->role !== 'admin' && !Auth::user()->hasRole('super_admin'))) {
            abort(403, "Seuls les administrateurs de l'espace peuvent inviter des membres.");
        }

        $validated = $request->validate([
            'utilisateur_id' => 'required|exists:utilisateurs,id',
            'role' => 'required|in:admin,membre',
        ]);

        $userToInvite = Utilisateur::findOrFail($validated['utilisateur_id']);
        if ($userToInvite->hasRole('invite') && $validated['role'] === 'admin') {
            return back()->with('error', "Un utilisateur externe ne peut pas être administrateur de l'espace.");
        }

        EspaceMembre::updateOrCreate(
            ['espace_id' => $espace->id, 'utilisateur_id' => $validated['utilisateur_id']],
            ['role' => $validated['role'], 'date_rejoint' => now()]
        );

        return back()->with('success', 'Membre ajouté avec succès.');
    }
}
