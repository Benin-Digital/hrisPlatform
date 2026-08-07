<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Annonce extends Model
{
    protected $table = 'annonces';

    protected $fillable = [
        'titre',
        'contenu',
        'image',
        'type_annonce',
        'auteur_id',
        'entite_id',
        'direction_id',
        'cible_type',
        'groupes_cibles',
        'utilisateurs_cibles',
        'est_epingle',
        'date_epingle_jusqua',
        'date_publication',
        'date_expiration',
        'visibilite',              // ← Nouveau
        'roles_cibles',            // ← Nouveau (array d'IDs ou noms de rôles)
        'directions_cibles',       // ← Nouveau (array)
    ];

    protected $casts = [
        'date_publication' => 'datetime',
        'date_epingle_jusqua' => 'datetime',
        'date_expiration' => 'datetime',
        'est_epingle' => 'boolean',
        'roles_cibles' => 'array',
        'groupes_cibles' => 'array',
        'directions_cibles' => 'array',
    ];

    // Relations
    public function createur()
    {
        return $this->belongsTo(Utilisateur::class, 'auteur_id');  // Aligné sur 'auteur_id' du fillable
    }

    public function direction()
    {
        return $this->belongsTo(Direction::class);
    }

    public function entite()
    {
        return $this->belongsTo(Entite::class);
    }

    /**
     * Scope pour filtrer les annonces visibles.
     * - Super-admin voit TOUT.
     * - Autres : basé sur visibilite + entite/roles/groupes/directions.
     */
    public function scopeVisible($query)
    {
        $user = auth()->user();

        // Super-admin bypass total
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            // Global : visible à tous
            $q->where('visibilite', 'global')

              // Par entité
              ->orWhere(function ($q) use ($user) {
                  $q->where('visibilite', 'entite')
                    ->where('entite_id', $user->entite_id);
              })

              // Par direction
              ->orWhere(function ($q) use ($user) {
                  $q->where('visibilite', 'directions')
                    ->whereJsonContains('directions_cibles', $user->direction_id);
              })

              // Par rôles
              ->orWhere(function ($q) use ($user) {
                  $q->where('visibilite', 'roles')
                    ->whereJsonContains('roles_cibles', $user->mainRole()?->nom);
              })

              // Par groupes (assume groupes_cibles = array d'user IDs)
              ->orWhere(function ($q) use ($user) {
                  $q->where('visibilite', 'groupes')
                    ->whereJsonContains('groupes_cibles', (string) $user->id);
              })
              
              // Extranet (pour les invités)
              ->orWhere(function ($q) use ($user) {
                  $q->where('visibilite', 'extranet')
                    ->when($user->mainRole()?->nom === 'invite', fn($sq) => $sq);
              });
        });
    }

    /**
     * Helper : Vérifie si l'annonce est visible pour un utilisateur donné.
     * Utile pour tests ou debug.
     */
    public function isVisibleForUser($user)
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Logique similaire au scope
        if ($this->visibilite === 'global') return true;
        if ($this->visibilite === 'entite' && $this->entite_id === $user->entite_id) return true;
        if ($this->visibilite === 'directions' && in_array($user->direction_id, $this->directions_cibles ?? [])) return true;
        if ($this->visibilite === 'roles' && in_array($user->mainRole()?->nom, $this->roles_cibles ?? [])) return true;
        if ($this->visibilite === 'groupes' && in_array((string) $user->id, $this->groupes_cibles ?? [])) return true;

        return false;
    }
}