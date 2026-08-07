<?php

namespace App\Http\Controllers;

use App\Models\Tache;
use App\Models\Utilisateur;
use App\Models\Entite;
use App\Notifications\TacheAssignee;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\TempsSession;
use carbon\Carbon;
use App\Events\NouvelleNotification;
class TacheController extends Controller
{
   public function index(Request $request)
{
    $user = Auth::user();
    $userId = $user->id;

    $query = Tache::with(['assigne', 'createur', 'entite']);

    if ($user->hasRole('super_admin')) {
        // Super admin voit toutes les tâches
        $query->latest();
    } elseif ($user->hasAnyRole(['manager', 'responsable_rh', 'admin_entite'])) {
        // Manager / RH / Admin Entité voit les tâches de son entité + les siennes
        $query->where(function ($q) use ($userId, $user) {
            $q->where('assigne_a', $userId)
              ->orWhere('createur_id', $userId)
              ->orWhere('entite_id', $user->entite_id);
        })->latest();
    } else {
        // Collaborateur, formateur, etc. voient uniquement leurs tâches
        $query->where(function ($q) use ($userId) {
            $q->where('assigne_a', $userId)
              ->orWhere('createur_id', $userId);
        })->latest();
    }

    $taches = $query->paginate(15);

    return Inertia::render('Taches/Index', [
        'taches' => $taches,
        'canCreate' => $user->hasAnyRole(['super_admin', 'admin_entite', 'manager', 'responsable_rh', 'formateur']),
    ]);
}

    public function create()
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['super_admin', 'admin_entite', 'manager', 'responsable_rh', 'formateur'])) {
            abort(403);
        }

        $queryUsers = Utilisateur::select('id', 'prenom', 'nom', 'entite_id');

        // Restriction : si non-super-admin, filtrer par entité
        if (!$user->hasRole('super_admin') && $user->entite_id) {
            $queryUsers->where('entite_id', $user->entite_id);
        }

        $utilisateurs = $queryUsers->orderBy('prenom')->get();

        $entites = Entite::select('id', 'nom')->get();

        return Inertia::render('Taches/Create', [
            'utilisateurs' => $utilisateurs,
            'entites' => $entites,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'titre'                  => 'required|string|max:255',
            'description'            => 'nullable|string',
            'entite_id'              => 'nullable|exists:entites,id',
            'assigne_a'              => 'nullable|exists:utilisateurs,id',
            'espace_id'              => 'nullable|exists:espaces_collaboratifs,id',
            'date_debut'             => 'nullable|date',
            'date_echeance'          => 'nullable|date|after_or_equal:date_debut',
            'priorite'               => 'required|in:basse,moyenne,haute',
            'statut'                 => 'required|in:en_attente,en_cours,terminee,annulee',
            'progression_pourcentage' => 'nullable|integer|min:0|max:100',
            'fichiers'               => 'nullable|array', // Si tu gères l'upload multiple
            'fichiers.*'             => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,png|max:10240',
        ]);

        $validated['createur_id'] = $user->id;

        // Restriction : si non-super-admin, forcer entite_id à la sienne
        if (!$user->hasRole('super_admin')) {
            if ($user->entite_id) {
                $validated['entite_id'] = $user->entite_id;
            }
            
            // Validation supplémentaire : l'assigné doit être dans la même entité (sauf si espace collaboratif ?)
            // Note: Dans un espace collaboratif, on peut avoir des gens d'autres entités.
            // On va assouplir la règle si un espace_id est présent.
            if (($validated['assigne_a'] ?? null) && !($validated['espace_id'] ?? null)) {
                $assigne = Utilisateur::findOrFail($validated['assigne_a']);
                if ($assigne->entite_id !== $user->entite_id) {
                    abort(403, "Vous ne pouvez assigner des tâches qu'aux membres de votre entité hors d'un espace collaboratif.");
                }
            }
        }

        $tache = Tache::create($validated);

    // Notification à l'assigné
    // Notification à l'assigné
if ($tache->assigne_a) {
    $assigne = Utilisateur::find($tache->assigne_a);
    if ($assigne) {
        // 1️ Stocker en base (via Notification Laravel)
        $assigne->notify(new \App\Notifications\TacheAssignee($tache));

        // 2️ Diffuser en temps réel (via Reverb)
        $notification = (object) [
            'id' => uniqid(),
            'data' => [
                'message' => "Vous avez été assigné à une nouvelle tâche : {$tache->titre}",
                'tache_id' => $tache->id,
                'titre' => $tache->titre,
            ],
            'created_at' => now(),
        ];
        broadcast(new NouvelleNotification($notification, $assigne->id));
    }
}
    // diffusion au super admin (tous les super admins) 
    $superAdmins = Utilisateur::whereHas('roles', function ($q) {
    $q->where('nom', 'super_admin');
})->get();

foreach ($superAdmins as $admin) {
    // Ne pas dupliquer si l'assigné est aussi super admin (déjà envoyé)
    if ($admin->id !== ($tache->assigne_a ?? 0)) {
        $notification = (object) [
            'id' => uniqid(),
            'data' => [
                'message' => "Nouvelle tâche créée par {$user->prenom} {$user->nom} : {$tache->titre}",
                'tache_id' => $tache->id,
                'titre' => $tache->titre,
                'createur' => $user->prenom . ' ' . $user->nom,
            ],
            'created_at' => now(),
        ];
        broadcast(new NouvelleNotification($notification, $admin->id));
    }
}

    // Gestion fichiers joints
        if ($request->hasFile('fichiers')) {
            $fichiers = [];
            foreach ($request->file('fichiers') as $file) {
                $path = $file->store('taches/' . $tache->id, 'local');
                $fichiers[] = [
                    'nom_original' => $file->getClientOriginalName(),
                    'nom_stocke'   => basename($path),
                    'chemin'       => $path,
                    'mime'         => $file->getMimeType(),
                    'taille'       => $file->getSize(),
                    'uploaded_at'  => now()->toDateTimeString(),
                ];
            }
            $tache->fichiers_joints = $fichiers;
            $tache->save();
        }

        if ($request->espace_id) {
            return back()->with('success', 'Tâche créée dans l\'espace !');
        }

        return redirect()->route('taches.index')
            ->with('success', 'Tâche créée avec succès !');
    }

    public function show($id)
    {
        $tache = Tache::with(['assigne', 'createur', 'entite'])->findOrFail($id);
        $user = Auth::user();

        // Droit de vue
        if ($tache->assigne_a !== $user->id &&
            $tache->createur_id !== $user->id &&
            !($user->hasRole('super_admin') || ($user->entite_id && $tache->entite_id === $user->entite_id))) {
            abort(403, 'Accès non autorisé à cette tâche.');
        }

        return Inertia::render('Taches/Show', [
            'tache' => $tache
        ]);
    }

    public function edit($id)
    {
        $tache = Tache::findOrFail($id);
        $user = Auth::user();

        if ($tache->createur_id !== $user->id && !$user->hasRole('super_admin')) {
            abort(403);
        }

        $queryUsers = Utilisateur::select('id', 'prenom', 'nom', 'entite_id');
        if (!$user->hasRole('super_admin') && $user->entite_id) {
            $queryUsers->where('entite_id', $user->entite_id);
        }
        $utilisateurs = $queryUsers->orderBy('prenom')->get();

        $entites = Entite::select('id', 'nom')->get();

        return Inertia::render('Taches/Edit', [
            'tache' => $tache,
            'utilisateurs' => $utilisateurs,
            'entites' => $entites,
        ]);
    }

    public function update(Request $request, $id)
    {
        $tache = Tache::findOrFail($id);
        $user = Auth::user();

        if ($tache->createur_id !== $user->id && !$user->hasRole('super_admin')) {
            abort(403);
        }

        $validated = $request->validate([
            'titre'                  => 'required|string|max:255',
            'description'            => 'nullable|string',
            'entite_id'              => 'nullable|exists:entites,id',
            'assigne_a'              => 'nullable|exists:utilisateurs,id',
            'date_debut'             => 'nullable|date',
            'date_echeance'          => 'nullable|date|after_or_equal:date_debut',
            'priorite'               => 'required|in:basse,moyenne,haute',
            'statut'                 => 'required|in:en_attente,en_cours,terminee,annulee',
            'progression_pourcentage' => 'nullable|integer|min:0|max:100',
        ]);

        // Automatisation date de fin réelle
    if ($validated['statut'] === 'terminee' && $tache->statut !== 'terminee') {
        $validated['date_fin_reelle'] = now();
    } elseif ($validated['statut'] !== 'terminee') {
        $validated['date_fin_reelle'] = null;
    }

    $tache->update($validated);

        // Gestion nouveaux fichiers joints
        if ($request->hasFile('fichiers')) {
            $existingFiles = $tache->fichiers_joints ?? [];
            foreach ($request->file('fichiers') as $file) {
                $path = $file->store('taches/' . $tache->id, 'local');
                $existingFiles[] = [
                    'nom_original' => $file->getClientOriginalName(),
                    'nom_stocke'   => basename($path),
                    'chemin'       => $path,
                    'mime'         => $file->getMimeType(),
                    'taille'       => $file->getSize(),
                    'uploaded_at'  => now()->toDateTimeString(),
                ];
            }
            $tache->fichiers_joints = $existingFiles;
            $tache->save();
        }

        return redirect()->route('taches.index')
            ->with('success', 'Tâche mise à jour avec succès.');
    }

    public function destroy($id)
    {
        $tache = Tache::findOrFail($id);
        $user = Auth::user();

        if ($tache->createur_id !== $user->id && !$user->hasRole('super_admin')) {
            abort(403);
        }

        // Supprimer fichiers joints si présents
        if ($tache->fichiers_joints) {
            foreach ($tache->fichiers_joints as $fichier) {
                Storage::disk('local')->delete($fichier['chemin']);
            }
        }

        $tache->delete();

        return redirect()->route('taches.index')
            ->with('success', 'Tâche supprimée avec succès.');
    }

    public function updateProgress(Request $request, $id)
    {
        $tache = Tache::findOrFail($id);
        $user = Auth::user();

        // Seul l'assigné ou le créateur/admin peut mettre à jour le progrès
        if ($tache->assigne_a !== $user->id && $tache->createur_id !== $user->id && !$user->hasRole('super_admin')) {
            abort(403);
        }

        $validated = $request->validate([
            'progression_pourcentage' => 'required|integer|min:0|max:100',
            'statut' => 'required|in:en_attente,en_cours,terminee,annulee',
        ]);

        $tache->update($validated);

        return redirect()->back()->with('success', 'Progression mise à jour !');
    }

    public function downloadFile($tacheId, $nomStocke)
    {
        $tache = Tache::findOrFail($tacheId);
        $user = Auth::user();

        // Droit d'accès
        if ($tache->assigne_a !== $user->id &&
            $tache->createur_id !== $user->id &&
            !$user->hasRole('super_admin')) {
            abort(403);
        }

        $fichiers = $tache->fichiers_joints ?? [];
        $fichier = collect($fichiers)->firstWhere('nom_stocke', $nomStocke);

        if (!$fichier) {
            abort(404, 'Fichier non trouvé.');
        }

        if (!Storage::disk('local')->exists($fichier['chemin'])) {
            abort(404, 'Fichier introuvable sur le disque.');
        }

        return Storage::disk('local')->download($fichier['chemin'], $fichier['nom_original']);
    }
    

    /**
 * Démarrer le chronomètre sur une tâche
 */
public function startTimer($id)
{
    $tache = Tache::findOrFail($id);
    $user = auth()->user();

    if ($tache->assigne_a !== $user->id && $tache->createur_id !== $user->id && !$user->hasRole('super_admin')) {
        abort(403);
    }

    // Vérifier si une session est déjà en cours
    $sessionEnCours = TempsSession::where('tache_id', $id)
        ->where('est_en_cours', true) // ← corrigé (underscores)
        ->where('utilisateur_id', $user->id)
        ->first();

    if ($sessionEnCours) {
        return response()->json(['error' => 'Un chronomètre est déjà en cours pour cette tâche.'], 409);
    }

    // Créer une nouvelle session
    TempsSession::create([
        'tache_id' => $id,
        'utilisateur_id' => $user->id,
        'debut' => now(),
        'est_en_cours' => true,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Chronomètre démarré.',
        'start_time' => now()->toDateTimeString(),
    ]);
}

/**
 * Arrêter le chronomètre sur une tâche
 */
public function stopTimer($id)
{
    $tache = Tache::findOrFail($id);
    $user = auth()->user();

    if ($tache->assigne_a !== $user->id && $tache->createur_id !== $user->id && !$user->hasRole('super_admin')) {
        abort(403);
    }
    //Récupérer la session en cours
    $session = TempsSession::where('tache_id', $id)
        ->where('est_en_cours', true)
        ->where('utilisateur_id', $user->id)
        ->first();

    //$startKey = "timer_start_{$id}";
    if (!$session) {
        return response () -> json(['error'=> 'Aucun chronomètre en cours.'], 404);
    }
    //calculer la durée écoulée
    $fin = now();
    $dureeSecondes = $fin->diffInSeconds($session->debut);

    // Mettre à jour la session
    $session->update([
        'fin' => $fin,
        'duree_secondes' => $dureeSecondes,
        'est_en_cours' => false,
    ]);
    
    //incrémenter le temps total de la tâche (en minutes, arrondi)
    $minutesToAdd = ceil($dureeSecondes / 60);
    $tache->increment('temps_passe_minutes', $minutesToAdd);


    return response()->json([
        'success' => true,
        'message' => 'Chronomètre arrêté.',
        'duree_secondes' => $dureeSecondes,
        'new_total' => $tache->temps_passe_minutes,
    ]);

}

public function getHistorique($id)
{
    $tache = Tache::findOrFail($id);
    $user = auth()->user();

    if ($tache->assigne_a !== $user->id && $tache->createur_id !== $user->id && !$user->hasRole('super_admin')) {
        abort(403);
    }

    $sessions = TempsSession::where('tache_id', $id)
        ->where('utilisateur_id', $user->id)
        ->where('est_en_cours', false)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($session) {
            return [
                'id' => $session->id,
                'debut' => $session->debut?->format('d/m/Y H:i'),
                'fin' => $session->fin?->format('d/m/Y H:i'),
                'duree' => $this->formatDuration($session->duree_secondes),
                'duree_secondes' => $session->duree_secondes,
            ];
        });

    // Ajouter la session en cours si elle existe
    $sessionEnCours = TempsSession::where('tache_id', $id)
        ->where('est_en_cours', true)
        ->where('utilisateur_id', $user->id)
        ->first();

    return response()->json([
        'sessions' => $sessions,
        'session_en_cours' => $sessionEnCours ? [
            'debut' => $sessionEnCours->debut?->format('d/m/Y H:i'),
            'depuis' => $sessionEnCours->debut?->diffForHumans(),
        ] : null,
    ]);
}

private function formatDuration($seconds)
{
    if ($seconds < 60) {
        return $seconds . 's';
    }
    $minutes = floor($seconds / 60);
    $secondsRest = $seconds % 60;
    if ($minutes < 60) {
        return $minutes . 'min ' . $secondsRest . 's';
    }
    $hours = floor($minutes / 60);
    $minutesRest = $minutes % 60;
    return $hours . 'h ' . $minutesRest . 'min';
}

// Mise à jour manuelle du temps passé (pour ajustement)
public function updateTemps(Request $request, $id)
{
    $tache = Tache::findOrFail($id);

    // Vérifier les droits
    if ($tache->assigne_a !== auth()->id() && $tache->createur_id !== auth()->id() && !auth()->user()->hasRole('super_admin')) {
        abort(403, 'Vous n\'êtes pas autorisé à modifier le temps de cette tâche.');
    }

    $validated = $request->validate([
        'temps_passe_minutes' => 'required|integer|min:0',
    ]);

    $tache->increment('temps_passe_minutes', $validated['temps_passe_minutes']);

    return response()->json([
        'success' => true,
        'new_total' => $tache->temps_passe_minutes,
    ]);
}

public function updateStatus(Request $request, $id)
{
    $tache = Tache::findOrFail($id);

    // Vérifier les droits
    $user = auth()->user();
    if ($tache->createur_id !== $user->id && $tache->assigne_a !== $user->id && !$user->hasRole('super_admin')) {
        abort(403, 'Vous ne pouvez pas modifier le statut de cette tâche.');
    }

    $validated = $request->validate([
        'statut' => 'required|in:en_attente,en_cours,terminee,annulee',
    ]);

    $tache->update(['statut' => $validated['statut']]);

    return response()->json(['success' => true]);
}
public function kanban(Request $request)
{
    $user = Auth::user();
    $userId = $user->id;

    $query = Tache::with(['assigne', 'createur', 'entite']);

    if ($user->hasRole('super_admin')) {
        $query->latest();
    } elseif ($user->hasAnyRole(['manager', 'responsable_rh', 'admin_entite'])) {
        $query->where(function ($q) use ($userId, $user) {
            $q->where('assigne_a', $userId)
              ->orWhere('createur_id', $userId)
              ->orWhere('entite_id', $user->entite_id);
        })->latest();
    } else {
        $query->where(function ($q) use ($userId) {
            $q->where('assigne_a', $userId)
              ->orWhere('createur_id', $userId);
        })->latest();
    }

    $taches = $query->get();

    //  Log pour déboguer
    \Log::info('Kanban - Nombre de tâches : ' . $taches->count());

    return Inertia::render('Taches/Kanban', [
        'taches' => $taches,
        'canCreate' => $user->hasAnyRole(['super_admin', 'admin_entite', 'manager', 'responsable_rh', 'formateur']),
    ]);
}


}

