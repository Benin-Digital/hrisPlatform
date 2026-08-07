<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Annonce;
use App\Models\Evenement;
use App\Models\Formation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

class ExtranetController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        if (!$user->hasRole('invite')) {
            abort(403, 'Accès réservé aux invités extranet.');
        }

        // ────────────────────────────────────────────
        // DOCUMENTS – inclut les partages "extranet"
        // ────────────────────────────────────────────
        $documents = Document::query()
            ->whereHas('partages', function ($q) use ($user) {
                $q->where(function ($sq) use ($user) {
                    // Partage direct avec l'utilisateur
                    $sq->where('partage_avec_type', 'App\\Models\\Utilisateur')
                       ->where('partage_avec_id', $user->id);
                })
                ->orWhere(function ($sq) use ($user) {
                    // Partage avec la direction
                    $sq->where('partage_avec_type', 'App\\Models\\Direction')
                       ->where('partage_avec_id', $user->direction_id);
                })
                ->orWhere(function ($sq) use ($user) {
                    // Partage avec l'entité
                    $sq->where('partage_avec_type', 'App\\Models\\Entite')
                       ->where('partage_avec_id', $user->entite_id);
                })
                ->orWhere(function ($sq) {
                    // Partage global vers tout l'extranet
                    $sq->where('partage_avec_type', 'extranet');
                });
            })
            ->with(['proprietaire' => fn($q) => $q->select('id', 'nom', 'prenom')])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(function ($doc) {
                return [
                    'uuid'         => $doc->uuid,
                    'titre'        => $doc->titre ?? $doc->nom_original,
                    'nom_original' => $doc->nom_original,
                    'description'  => $doc->description ?? null,
                    'created_at'   => $doc->created_at->toDateString(),
                    'proprietaire' => $doc->proprietaire 
                        ? $doc->proprietaire->prenom . ' ' . $doc->proprietaire->nom 
                        : '—',
                ];
            });

        // ────────────────────────────────────────────
        // ACTUALITÉS / ANNONCES
        // ────────────────────────────────────────────
        $actualites = Annonce::visible()
            ->where(function ($q) use ($user) {
                $q->where('visibilite', 'global')
                  ->orWhere(function ($sq) use ($user) {
                      $sq->where('visibilite', 'entite')
                         ->where('entite_id', $user->entite_id);
                  })
                  ->orWhereJsonContains('roles_cibles', 'invite')
                  ->orWhereJsonContains('groupes_cibles', (string) $user->id)
                  ->orWhere(function ($sq) use ($user) {
                      $sq->where('visibilite', 'directions')
                         ->whereJsonContains('directions_cibles', $user->direction_id);
                  });
            })
            ->with('createur:id,nom,prenom')
            ->orderByDesc('date_publication')
            ->orderByDesc('est_epingle')
            ->limit(6)
            ->get()
            ->map(fn($a) => [
                'id'              => $a->id,
                'titre'           => $a->titre,
                'extrait'         => substr(strip_tags($a->contenu ?? ''), 0, 140) . '...',
                'date'            => $a->date_publication 
                    ? $a->date_publication->format('d M Y') 
                    : $a->created_at->format('d M Y'),
                'epingle'         => $a->est_epingle ?? false,
                'auteur'          => $a->createur 
                    ? $a->createur->prenom . ' ' . $a->createur->nom 
                    : '—',
            ]);

        // ────────────────────────────────────────────
        // ÉVÉNEMENTS (seulement les futurs ou en cours)
        // ────────────────────────────────────────────
        $evenements = Evenement::visible()
            ->where(function ($q) use ($user) {
                $q->where('visibilite', 'global')
                  ->orWhere(function ($sq) use ($user) {
                      $sq->where('visibilite', 'entite')
                         ->where('entite_id', $user->entite_id);
                  })
                  ->orWhereJsonContains('roles_cibles', 'invite')
                  ->orWhereJsonContains('groupes_cibles', (string) $user->id)
                  ->orWhere(function ($sq) use ($user) {
                      $sq->where('visibilite', 'directions')
                         ->whereJsonContains('directions_cibles', $user->direction_id);
                  });
            })
            ->where(function ($q) {
                $q->where('date_debut', '>=', Carbon::today()->startOfDay())
                  ->orWhere('date_fin', '>=', Carbon::now());
            })
            ->with('organisateur:id,nom,prenom')
            ->orderBy('date_debut')
            ->limit(6)
            ->get()
            ->map(fn($e) => [
                'id'         => $e->id,
                'titre'      => $e->titre,
                'debut'      => $e->date_debut->format('d M Y H:i'),
                'lieu'       => $e->lieu ?? $e->lien_virtuel ?? 'En ligne / non précisé',
                'organisateur'=> $e->organisateur 
                    ? $e->organisateur->prenom . ' ' . $e->organisateur->nom 
                    : '—',
            ]);

        // ────────────────────────────────────────────
        // FORMATIONS (publiques ou accessibles)
        // ────────────────────────────────────────────
        $formations = Formation::where('statut', '!=', 'archive')
            ->where(function ($q) {
                $q->where('est_public', true)
                  ->orWhere('mode_acces', 'public');
            })
            ->with(['categorie', 'formateur:id,nom,prenom'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($f) => [
                'id'    => $f->id,
                'titre' => $f->titre,
                'duree' => $f->duree_minutes 
                    ? round($f->duree_minutes / 60, 1) . ' h' 
                    : '—',
                'debut' => $f->date_debut 
                    ? $f->date_debut->format('d M Y') 
                    : '—',
            ]);

        return Inertia::render('Extranet/Dashboard', [
            'documents'  => $documents,
            'actualites' => $actualites,
            'evenements' => $evenements,
            'formations' => $formations,
        ]);
    }
}