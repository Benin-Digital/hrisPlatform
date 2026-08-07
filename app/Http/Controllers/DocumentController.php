<?php

namespace App\Http\Controllers;

use App\Models\DossierDocument;
use App\Models\Document;
use App\Models\HistoriqueDocument;
use App\Models\PartageDocument;
use App\Models\Utilisateur;
use App\Events\NouvelleNotification;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;


class DocumentController extends Controller
{
    public function index(Request $request, $chemin = null)
    {
        $user = auth()->user();
        $this->ensureRootFolder();

        $dossierCourant = null;
        $cheminComplet = '';

        if ($chemin) {
            $cheminComplet = $chemin;
            $queryDossier = DossierDocument::where('chemin_complet', $cheminComplet);

            if (!$user->hasRole('super_admin')) {
                $queryDossier->where(function ($q) use ($user) {
                    $q->where('entite_id', $user->entite_id)
                        ->orWhereNull('entite_id');
                });
            }
            $dossierCourant = $queryDossier->firstOrFail();
        }

        $queryFolders = DossierDocument::where('dossier_parent_id', $dossierCourant?->id);

        // Base query for documents
        $queryDocs = Document::with('proprietaire');

        // Filter by folder if specified, otherwise show all accessible documents at root
        if ($dossierCourant) {
            $queryDocs->where('dossier_id', $dossierCourant->id);
        } else {
            // If no folder, we might want to show documents that are not in a folder or all documents
            // Let's show all accessible documents when at the root for better visibility as requested.
            // Comment out or adjust if you only want documents with dossier_id = null
            // $queryDocs->whereNull('dossier_id'); 
        }

        if (!$user->hasRole('super_admin')) {
            // Folders visible to the user: their entity or global folders
            $queryFolders->where(function ($q) use ($user) {
                $q->where('entite_id', $user->entite_id)
                    ->orWhereNull('entite_id');
            });

            // Documents visible to the user:
            // 1. Documents belonging to the user's entity
            // 2. OR documents shared with the user (directly or via entity/direction/etc.)
            // 3. OR global documents (entite_id is null)
            $queryDocs->where(function ($q) use ($user) {
                $q->where(function ($sq) use ($user) {
                    $sq->where('entite_id', $user->entite_id)
                        ->orWhereNull('entite_id');
                })->orWhereHas('partages', function ($sq) use ($user) {
                    $sq->where(function ($ssq) use ($user) {
                        $ssq->where('partage_avec_type', 'utilisateur')->where('partage_avec_id', $user->id);
                    })->orWhere(function ($ssq) use ($user) {
                        $ssq->where('partage_avec_type', 'entite')->where('partage_avec_id', $user->entite_id);
                    })->orWhere(function ($ssq) use ($user) {
                        $ssq->where('partage_avec_type', 'direction')->where('partage_avec_id', $user->direction_id);
                    })->orWhere('partage_avec_type', 'global')
                        ->orWhere('partage_avec_type', 'extranet');
                });
            });
        }

        $dossiers = $queryFolders->orderBy('nom')->get();
        $documents = $queryDocs->orderBy('created_at', 'desc')->get();

        // If at root and no folder-bound shared docs, we could also fetch ALL shared docs not in these folders
        // but for now, let's stick to the folder structure if folders are used.
        // If the user is on the "Racine", maybe they want to see "Shared with me" separately?
        // For simplicity, let's just make sure visibility is correct within folders.

        $breadcrumb = [];
        if ($dossierCourant) {
            $breadcrumb = $this->getBreadcrumb($dossierCourant);
        }

        // Récupérer la liste des utilisateurs pour le partage (si autorisé)
        $users = [];
        $entites = [];
        $allowedRoles = ['super_admin', 'admin_entite', 'manager', 'responsable_rh', 'formateur'];
        $canShare = false;
        foreach ($allowedRoles as $roleName) {
            if ($user->hasRole($roleName)) {
                $canShare = true;
                break;
            }
        }

        if ($canShare) {
            $users = Utilisateur::select('id', 'nom', 'prenom', 'entite_id')->orderBy('nom')->get();
            if ($user->hasRole('super_admin')) {
                $entites = \App\Models\Entite::select('id', 'nom')->orderBy('nom')->get();
            }
        }

        return Inertia::render('Documents/Index', [
            'dossierCourant' => $dossierCourant,
            'dossiers' => $dossiers,
            'documents' => $documents,
            'breadcrumb' => $breadcrumb,
            'cheminComplet' => $cheminComplet,
            'users' => $users,
            'entites' => $entites,
            'canCreate' => $canShare,
        ]);
    }

    private function getBreadcrumb($dossier)
    {
        $breadcrumb = [];
        $current = $dossier;

        while ($current) {
            array_unshift($breadcrumb, [
                'id' => $current->id,
                'nom' => $current->nom,
                'chemin' => $current->chemin_complet,
            ]);
            $current = $current->parent;
        }

        return $breadcrumb;
    }

    public function storeDossier(Request $request)
    {
        if (
            !Auth::user()->hasRole('super_admin') &&
            !Auth::user()->hasRole('admin_entite') &&
            !Auth::user()->hasRole('manager') &&
            !Auth::user()->hasRole('responsable_rh')
        ) {
            abort(403);
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'dossier_parent_id' => 'nullable|exists:dossiers_documents,id',
            'visibilite' => 'required|in:public,entite,direction,prive,groupe',
        ]);

        // 🔍 Vérifier si un dossier du même nom existe déjà sous le même parent
        $existing = DossierDocument::where('nom', $validated['nom'])
            ->where('dossier_parent_id', $validated['dossier_parent_id'] ?? null)
            ->exists();

        if ($existing) {
            return back()->withErrors(['nom' => 'Un dossier portant ce nom existe déjà à cet emplacement.']);
        }

        $parent = null;
        $cheminComplet = $validated['nom'];

        if ($validated['dossier_parent_id']) {
            $parent = DossierDocument::findOrFail($validated['dossier_parent_id']);
            $cheminComplet = $parent->chemin_complet . '/' . $validated['nom'];
        }

        DossierDocument::create([
            'uuid' => Str::uuid(),
            'nom' => $validated['nom'],
            'dossier_parent_id' => $validated['dossier_parent_id'],
            'entite_id' => auth()->user()->entite_id,
            'creer_par' => auth()->id(),
            'visibilite' => $validated['visibilite'],
            'chemin_complet' => $cheminComplet,
            'est_actif' => true,
        ]);

        return redirect()->back()->with('success', 'Dossier créé avec succès');
    }

    public function upload(Request $request, $dossier_id = null)
    {
        $user = auth()->user();
        $targetEntiteId = $user->entite_id;

        if ($user->hasRole('super_admin') && $request->has('entite_id')) {
            $targetEntiteId = $request->entite_id === 'global' ? null : $request->entite_id;
        }

        if ($dossier_id && $dossier_id !== 'root') {
            $dossier = DossierDocument::findOrFail($dossier_id);
        } else {
            $dossier = $this->ensureRootFolder($targetEntiteId);
        }

        $request->validate([
            'documents.*' => 'required|file|max:51200', // 50MB max
            'espace_id' => 'nullable|exists:espaces_collaboratifs,id',
        ]);

        foreach ($request->file('documents') as $file) {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $mimeType = $file->getMimeType();
            $fileSize = $file->getSize();
            $filename = Str::uuid() . '.' . $extension;

            $storageSubDir = $targetEntiteId ?? 'global';
            $relativePath = $file->storeAs(
                'documents/' . $storageSubDir,
                $filename,
                'local'
            );

            if (!Storage::disk('local')->exists($relativePath)) {
                \Log::error('Échec upload : fichier non stocké à ' . $relativePath);
                continue;
            }

            $document = Document::create([
                'uuid' => Str::uuid(),
                'nom_fichier' => $filename,
                'nom_original' => $originalName,
                'extension' => $extension,
                'mime_type' => $mimeType,
                'taille_octets' => $fileSize,
                'dossier_id' => $dossier->id,
                'chemin_storage' => $relativePath,
                'proprietaire_id' => $user->id,
                'entite_id' => $targetEntiteId,
                'direction_id' => $user->direction_id, // Toujours la direction du déposant
                'espace_id' => $request->espace_id,
            ]);

            $this->logAction($document, 'creation', 'Upload depuis le navigateur');
        }

        return back()->with('success', 'Fichier(s) uploadé(s) avec succès');
    }

    public function view($uuid)
    {
        $user = auth()->user();
        $document = Document::where('uuid', $uuid);

        if (!$user->hasRole('super_admin')) {
            $document->where(function ($q) use ($user) {
                // Document belongs to user's entity
                $q->where('entite_id', $user->entite_id)
                    // OR Document is global
                    ->orWhereNull('entite_id')
                    // OR User is the owner
                    ->orWhere('proprietaire_id', $user->id)
                    // OR Document is shared with the user
                    ->orWhereHas('partages', function ($sq) use ($user) {
                        $sq->where(function ($ssq) use ($user) {
                            $ssq->where('partage_avec_type', 'utilisateur')->where('partage_avec_id', $user->id);
                        })->orWhere(function ($ssq) use ($user) {
                            $ssq->where('partage_avec_type', 'entite')->where('partage_avec_id', $user->entite_id);
                        })->orWhere(function ($ssq) use ($user) {
                            $ssq->where('partage_avec_type', 'direction')->where('partage_avec_id', $user->direction_id);
                        })->orWhere('partage_avec_type', 'global')
                            ->orWhere('partage_avec_type', 'extranet');
                    })->orWhereIn('espace_id', $user->espaces->pluck('id'));
            });
        }

        $document = $document->firstOrFail();

        $this->logAction($document, 'visualisation');

        if (!Storage::disk('local')->exists($document->chemin_storage)) {
            abort(404, 'Fichier introuvable sur le serveur');
        }

        return Storage::disk('local')->response($document->chemin_storage, $document->nom_original, [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline',
        ]);
    }

    public function download($uuid)
    {
        $user = auth()->user();
        $document = Document::where('uuid', $uuid);

        if (!$user->hasRole('super_admin')) {
            $document->where(function ($q) use ($user) {
                // Document belongs to user's entity
                $q->where('entite_id', $user->entite_id)
                    // OR Document is global
                    ->orWhereNull('entite_id')
                    // OR User is the owner
                    ->orWhere('proprietaire_id', $user->id)
                    // OR Document is shared with the user
                    ->orWhereHas('partages', function ($sq) use ($user) {
                        $sq->where(function ($ssq) use ($user) {
                            $ssq->where('partage_avec_type', 'utilisateur')->where('partage_avec_id', $user->id);
                        })->orWhere(function ($ssq) use ($user) {
                            $ssq->where('partage_avec_type', 'entite')->where('partage_avec_id', $user->entite_id);
                        })->orWhere(function ($ssq) use ($user) {
                            $ssq->where('partage_avec_type', 'direction')->where('partage_avec_id', $user->direction_id);
                        })->orWhere('partage_avec_type', 'global')
                            ->orWhere('partage_avec_type', 'extranet');
                    })->orWhereIn('espace_id', $user->espaces->pluck('id'));
            });
        }

        $document = $document->firstOrFail();

        $this->logAction($document, 'telechargement');

        if (!Storage::disk('local')->exists($document->chemin_storage)) {
            abort(404, 'Fichier introuvable sur le serveur');
        }

        return Storage::disk('local')->download($document->chemin_storage, $document->nom_original);
    }

    public function destroy($uuid)
    {
        $query = Document::where('uuid', $uuid);

        if (!auth()->user()->hasRole('super_admin')) {
            $query->where(function ($q) {
                $q->where('proprietaire_id', auth()->id())
                    ->orWhere('entite_id', auth()->user()->entite_id);
            });
        }

        $document = $query->firstOrFail();

        // Log avant suppression
        $this->logAction($document, 'suppression');

        // Supprimer le fichier physique
        if (Storage::disk('local')->exists($document->chemin_storage)) {
            Storage::disk('local')->delete($document->chemin_storage);
        }

        // Supprimer de la base
        $document->delete();

        return back()->with('success', 'Document supprimé avec succès');
    }

    //use App\Events\NouvelleNotification;
    //use App\Models\Utilisateur;
    //use Illuminate\Validation\Rule;

    public function partager(Request $request, $uuid)
{
    $user = auth()->user();
    $document = Document::where('uuid', $uuid);

    if (!$user->hasRole('super_admin')) {
        $document->where('entite_id', $user->entite_id);
    }

    $document = $document->firstOrFail();

    $allowedRoles = ['super_admin', 'admin_entite', 'manager', 'responsable_rh', 'formateur'];
    $canShare = false;
    foreach ($allowedRoles as $roleName) {
        if ($user->hasRole($roleName)) {
            $canShare = true;
            break;
        }
    }

    if (!$canShare) {
        abort(403, "Vous n'avez pas le droit de partager des documents.");
    }

    $validated = $request->validate([
        'type' => 'required|in:utilisateur,direction,entite,extranet,global',
        'id' => [
            'nullable',
            'integer',
            Rule::requiredIf(fn() => in_array($request->type, ['utilisateur', 'direction', 'entite'])),
            Rule::when(
                in_array($request->type, ['utilisateur', 'direction', 'entite']),
                fn() => Rule::exists(match ($request->type) {
                    'utilisateur' => 'utilisateurs',
                    'direction'   => 'directions',
                    'entite'      => 'entites',
                }, 'id')
            ),
        ],
        'permissions' => 'required|in:lecture,telechargement',
    ]);

    if (!$user->hasRole('super_admin')) {
        if ($validated['type'] === 'entite' && (int)$validated['id'] !== (int)$user->entite_id) {
            abort(403, "Vous ne pouvez partager qu'avec votre propre entité.");
        }
        if ($validated['type'] === 'global') {
            abort(403, "Seuls les super administrateurs peuvent effectuer un partage global.");
        }
    }

    // ======= NOTIFICATIONS =======

    $destinataires = [];

    if ($validated['type'] === 'utilisateur') {
        $destinataires = [(int)$validated['id']];
    } elseif ($validated['type'] === 'entite') {
        $destinataires = Utilisateur::where('entite_id', (int)$validated['id'])->pluck('id')->toArray();
    } elseif ($validated['type'] === 'direction') {
        $destinataires = Utilisateur::where('direction_id', (int)$validated['id'])->pluck('id')->toArray();
    }
    // Pour 'global' et 'extranet', on ne notifie personne (sauf les Super Admins)

    $superAdmins = Utilisateur::whereHas('roles', function ($q) {
        $q->where('nom', 'super_admin');
    })->pluck('id')->toArray();

    $tousDestinataires = array_unique(array_merge($destinataires, $superAdmins));

    $notification = (object) [
        'id' => uniqid(),
        'data' => [
            'message' => "Le document '{$document->nom_original}' a été partagé avec vous.",
            'document_id' => $document->id,
        ],
    ];

    foreach ($tousDestinataires as $destId) {
        if ($destId != $user->id) {
            broadcast(new NouvelleNotification($notification, $destId));
        }
    }

    // ======= CRÉATION DU PARTAGE =======

    PartageDocument::create([
        'document_id'       => $document->id,
        'partage_avec_type' => $validated['type'],
        'partage_avec_id'   => in_array($validated['type'], ['global', 'extranet']) ? null : $validated['id'],
        'partage_par'       => $user->id,
        'permissions'       => $validated['permissions'],
    ]);

    $this->logAction(
        $document,
        'partage',
        "Type: {$validated['type']} | ID: " . ($validated['id'] ?? 'null') . " | Permissions: {$validated['permissions']}"
    );

    return back()->with('success', 'Document partagé avec succès.');
}

    private function logAction($document, $action, $details = null)
    {
        HistoriqueDocument::create([
            'document_id' => $document->id,
            'utilisateur_id' => auth()->id(),
            'action' => $action,
            'details' => $details,
            'ip_adresse' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private function ensureRootFolder($entiteId = null)
    {
        // Si entiteId n'est pas passé, on prend celle du user (comportement par défaut)
        if (func_num_args() === 0) {
            $entiteId = auth()->user()?->entite_id;
        }

        $root = DossierDocument::whereNull('dossier_parent_id')
            ->where('entite_id', $entiteId)
            ->where('nom', 'Racine')
            ->first();

        if (!$root) {
            $root = DossierDocument::create([
                'uuid' => Str::uuid(),
                'nom' => 'Racine',
                'dossier_parent_id' => null,
                'entite_id' => $entiteId,
                'creer_par' => auth()->id(),
                'visibilite' => $entiteId ? 'entite' : 'public',
                'chemin_complet' => 'Racine',
                'est_actif' => true,
            ]);
        }

        return $root;
    }
}
