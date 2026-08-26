<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\CategorieFormation;
use App\Models\Utilisateur;
use App\Models\Entite;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class FormationController extends Controller
{
    /**
     * Identifiant fictif utilisé pour représenter l'entité "EXTRANET" dans les sélecteurs.
     * Aucune entité réelle ne doit avoir cet ID en base.
     */
    private const EXTRANET_ENTITE_ID = 999999;

    public function index(Request $request) // ← AJOUT DU PARAMÈTRE $request
    {
        $user = Auth::user();
        $canCreate = $user->hasRole('super_admin') || $user->hasRole('admin_entite') || $user->hasRole('manager') || $user->hasRole('formateur');
        $canManage = $user->hasRole('super_admin') || $user->hasRole('formateur');

        $query = Formation::where('statut', '!=', 'archive')
            ->with(['categorie', 'formateur']);

        // Filtres
        if ($request->filled('categorie')) {
            $query->where('categorie_id', $request->categorie);
        }
        if ($request->filled('niveau')) {
            $query->where('niveau', $request->niveau);
        }
        if ($request->filled('duree_min')) {
            $query->where('duree_minutes', '>=', (int)$request->duree_min);
        }
        if ($request->filled('duree_max')) {
            $query->where('duree_minutes', '<=', (int)$request->duree_max);
        }
        if ($request->filled('mode_acces')) {
            $query->where('mode_acces', $request->mode_acces);
        }

        //  Recherche full-text
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titre', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('sous_titre', 'LIKE', "%{$search}%");
            });
        }

        // Si simple collaborateur, ne voir que les formations publiées de son entité
        if (!$canCreate) {
            $query->where('statut', 'publie')
                  ->where('entite_id', $user->entite_id);
        }

        $formations = $query->orderBy('created_at', 'desc')->paginate(12)->appends($request->query());

        $categories = CategorieFormation::all();

        return Inertia::render('Formations/Index', [
            'formations' => $formations,
            'categories' => $categories,
            'canCreate'  => $canCreate,
            'canManage'  => $canManage,
            'filters'    => $request->only(['search', 'categorie', 'niveau', 'duree_min', 'duree_max', 'mode_acces']),
        ]);
    }

    // ... le reste des méthodes (show, create, store, etc.)

    public function show($id)
    {
        $formation = Formation::with(['sequences.lecons', 'inscriptions', 'categorie', 'formateur'])->findOrFail($id);
        $user = Auth::user();
        $inscription = $formation->inscriptions->where('utilisateur_id', $user->id)->first();

        $canManage = $user->hasRole('super_admin') || $user->hasRole('formateur');

        return Inertia::render('Formations/Show', [
            'formation' => $formation,
            'inscription' => $inscription,
            'canManage' => $canManage,
        ]);
    }

    public function create()
    {
        if (!Auth::user()->hasRole('super_admin') && 
            !Auth::user()->hasRole('admin_entite') && 
            !Auth::user()->hasRole('manager') && 
            !Auth::user()->hasRole('formateur')) {
            abort(403, 'Action non autorisée.');
        }

        $categories = CategorieFormation::all();
        $entites = Entite::select('id', 'nom')->orderBy('nom')->get();

        // L'option EXTRANET est disponible pour tous ceux qui peuvent créer
        $entites->push((object)[
            'id' => self::EXTRANET_ENTITE_ID,
            'nom' => 'EXTRANET (Partenaires)'
        ]);

        $users = Utilisateur::select('id', 'prenom', 'nom', 'entite_id')
            ->with('entite:id,nom')
            ->orderBy('nom')
            ->get();

        return Inertia::render('Formations/Create', [
            'categories' => $categories,
            'entites' => $entites,
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('super_admin') && 
            !$user->hasRole('admin_entite') && 
            !$user->hasRole('manager') && 
            !$user->hasRole('formateur')) {
            abort(403);
        }

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'sous_titre' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'objectifs' => 'nullable|string',
            'prerequis' => 'nullable|string',
            'niveau' => 'required|in:debutant,intermediaire,avance,expert',
            'duree_minutes' => 'required|integer|min:0',
            'categorie_id' => 'nullable|exists:categories_formations,id',
            'entite_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value != self::EXTRANET_ENTITE_ID && !\App\Models\Entite::where('id', $value)->exists()) {
                        $fail($attribute . ' is invalid.');
                    }
                }
            ],
            'formateur_principal_id' => 'nullable|exists:utilisateurs,id',
            'lien_session' => 'nullable|url',
            'mode_acces' => 'required|in:interne,externe,mixte',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'date_limite_inscription' => 'nullable|date|before_or_equal:date_debut',
            'capacite_max' => 'nullable|integer|min:1',
            'statut' => 'required|in:brouillon,publie,archive',
            'fichiers' => 'nullable|array',
            'fichiers.*' => 'file|max:10240',
        ]);

        $validated['uuid'] = (string) Str::uuid();
        $validated['formateur_principal_id'] = $validated['formateur_principal_id'] ?? Auth::id();
        
        $baseSlug = Str::slug($validated['titre']);
        $uniqueSlug = $baseSlug;
        $counter = 1;
        while (Formation::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $counter++;
        }
        $validated['slug'] = $uniqueSlug;
        
        if ($request->hasFile('fichiers')) {
            $fichiersJoints = [];
            foreach ($request->file('fichiers') as $file) {
                $path = $file->store('formations/documents', 'local');
                $fichiersJoints[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ];
            }
            $validated['fichiers_joints'] = $fichiersJoints;
        }

        if ($validated['statut'] === 'publie') {
            $validated['publie_at'] = now();
        }

        // Gestion cible Extranet virtuelle
        if ($validated['entite_id'] == self::EXTRANET_ENTITE_ID) {
            $validated['entite_id'] = null;
            $validated['mode_acces'] = 'externe';
        }

        Formation::create($validated);

        return redirect()->route('formations.index')->with('success', 'Formation créée avec succès !');
    }

    public function edit($id)
    {
        $formation = Formation::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasRole('super_admin') && !$user->hasRole('formateur')) {
            abort(403, 'Seuls les administrateurs et formateurs peuvent modifier des formations.');
        }

        $categories = CategorieFormation::all();
        $entites = Entite::select('id', 'nom')->orderBy('nom')->get();

        $entites->push((object)[
            'id' => self::EXTRANET_ENTITE_ID,
            'nom' => 'EXTRANET (Partenaires)'
        ]);

        $users = Utilisateur::select('id', 'prenom', 'nom', 'entite_id')
            ->with('entite:id,nom')
            ->orderBy('nom')
            ->get();

        return Inertia::render('Formations/Edit', [
            'formation' => $formation,
            'categories' => $categories,
            'entites' => $entites,
            'users' => $users,
        ]);
    }

    public function update(Request $request, $id)
    {
        $formation = Formation::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasRole('super_admin') && !$user->hasRole('formateur')) {
            abort(403);
        }

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'sous_titre' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'objectifs' => 'nullable|string',
            'prerequis' => 'nullable|string',
            'niveau' => 'required|in:debutant,intermediaire,avance,expert',
            'duree_minutes' => 'required|integer|min:0',
            'categorie_id' => 'nullable|exists:categories_formations,id',
            'entite_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value != self::EXTRANET_ENTITE_ID && !\App\Models\Entite::where('id', $value)->exists()) {
                        $fail($attribute . ' is invalid.');
                    }
                }
            ],
            'formateur_principal_id' => 'nullable|exists:utilisateurs,id',
            'lien_session' => 'nullable|url',
            'mode_acces' => 'required|in:interne,externe,mixte',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'date_limite_inscription' => 'nullable|date|before_or_equal:date_debut',
            'capacite_max' => 'nullable|integer|min:1',
            'statut' => 'required|in:brouillon,publie,archive',
            'fichiers' => 'nullable|array',
            'fichiers.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip',
        ]);

        $baseSlug = Str::slug($validated['titre']);
        $uniqueSlug = $baseSlug;
        $counter = 1;
        while (Formation::where('slug', $uniqueSlug)->where('id', '!=', $id)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $counter++;
        }
        $validated['slug'] = $uniqueSlug;
        $validated['formateur_principal_id'] = $validated['formateur_principal_id'] ?? $formation->formateur_principal_id;

        if ($request->hasFile('fichiers')) {
            $existingFichiers = $formation->fichiers_joints ?? [];
            foreach ($request->file('fichiers') as $file) {
                $path = $file->store('formations/documents', 'local');
                $existingFichiers[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ];
            }
            $validated['fichiers_joints'] = $existingFichiers;
        }

        if ($validated['statut'] === 'publie' && !$formation->publie_at) {
            $validated['publie_at'] = now();
        }

        // Gestion cible Extranet virtuelle
        if ($validated['entite_id'] == self::EXTRANET_ENTITE_ID) {
            $validated['entite_id'] = null;
            $validated['mode_acces'] = 'externe';
        }

        $formation->update($validated);

        return redirect()->route('formations.index')->with('success', 'Formation mise à jour !');
    }

    public function destroy($id)
    {
        $formation = Formation::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasRole('super_admin') && !$user->hasRole('formateur')) {
            abort(403);
        }

        $formation->delete();

        return redirect()->route('formations.index')->with('success', 'Formation supprimée.');
    }

    public function inscrire($id)
    {
        $formation = Formation::findOrFail($id);
        $user = Auth::user();

        $dejaInscrit = $formation->inscriptions()->where('utilisateur_id', $user->id)->exists();
        if ($dejaInscrit) {
            return back()->with('error', 'Vous êtes déjà inscrit à cette formation.');
        }

        if ($formation->capacite_max > 0 && $formation->nombre_inscrits >= $formation->capacite_max) {
            return back()->with('error', 'Désolé, cette formation est complète.');
        }

        $formation->inscriptions()->create([
            'utilisateur_id' => $user->id,
            'statut' => 'inscrit',
            'progression_pourcentage' => 0,
        ]);

        $formation->increment('nombre_inscrits');

        return back()->with('success', 'Votre inscription a été validée avec succès !');
    }

    public function downloadDocument($id, $fileName)
    {
        $formation = Formation::findOrFail($id);
        $user = Auth::user();

        $isRegistered = $formation->inscriptions()->where('utilisateur_id', $user->id)->exists();
        $isAdminOrTrainer = $user->hasRole('super_admin') || $formation->formateur_principal_id === $user->id;

        if (!$isRegistered && !$isAdminOrTrainer) {
            abort(403, "Vous n'avez pas accès à ce document.");
        }

        $fichiers = $formation->fichiers_joints ?? [];
        $fileData = collect($fichiers)->firstWhere('name', $fileName);

        if (!$fileData) {
            abort(404, "Document non trouvé.");
        }

        if (!Storage::disk('local')->exists($fileData['path'])) {
            abort(404, "Fichier introuvable sur le disque.");
        }

        return Storage::disk('local')->download($fileData['path'], $fileData['name']);
    }

    public function storeEvaluation(Request $request, $id)
    {
        $formation = Formation::findOrFail($id);
        $user = Auth::user();

        $inscription = $formation->inscriptions()->where('utilisateur_id', $user->id)->first();
        if (!$inscription) {
            return back()->with('error', "Vous devez être inscrit pour donner votre avis.");
        }

        $validated = $request->validate([
            'note' => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        $formation->evaluations()->updateOrCreate(
            ['utilisateur_id' => $user->id],
            [
                'note' => $validated['note'],
                'commentaire' => $validated['commentaire']
            ]
        );

        $formation->update([
            'note_moyenne' => $formation->evaluations()->avg('note'),
            'nombre_evaluations' => $formation->evaluations()->count()
        ]);

        return back()->with('success', "Merci pour votre avis !");
    }
}