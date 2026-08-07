<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Evenement extends Model
{
    protected $table = 'evenements';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'titre', 'description', 'date_debut', 'date_fin', 'fuseau_horaire', 'duree_minutes',
        'type_evenement', 'categorie', 'couleur', 'lieu', 'lien_virtuel', 'type_lieu',
        'organisateur_id', 'entite_id', 'direction_id', 'formation_id',
        'visibilite', 'groupes_acces', 'capacite_max', 'inscription_requise',
        'est_recurrent', 'recurrence_pattern', 'date_fin_recurrence',
        'statut', 'nombre_participants', 'nombre_inscrits',
        // Nouveaux champs pour visibilité et priorisation globale
        'roles_cibles', 'groupes_cibles', 'directions_cibles',
        'est_epingle', 'date_epingle_jusqua',
    ];

    protected $casts = [
        'date_debut'           => 'datetime',
        'date_fin'             => 'datetime',
        'date_fin_recurrence'  => 'date',
        'est_recurrent'        => 'boolean',
        'inscription_requise'  => 'boolean',
        'groupes_acces'        => 'array',
        'recurrence_pattern'   => 'array',
        'roles_cibles'         => 'array',
        'groupes_cibles'       => 'array',
        'directions_cibles'    => 'array',
        'est_epingle'          => 'boolean',
        'date_epingle_jusqua'  => 'datetime',
    ];

    // Relations
    public function organisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'organisateur_id');
    }

    public function direction()
    {
        return $this->belongsTo(Direction::class);
    }

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    /**
     * Scope pour filtrer les événements visibles.
     * - Super-admin voit TOUT (aucun filtre)
     * - Autres utilisateurs : selon visibilite + entité/roles/groupes/directions
     */
    public function scopeVisible($query)
    {
        $user = auth()->user();

        // Super-admin a accès total (bypass tous les filtres)
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            // Visibilité globale : tout le monde voit
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

              // Par groupes (groupes_cibles = array d'IDs utilisateurs)
              ->orWhere(function ($q) use ($user) {
                  $q->where('visibilite', 'groupes')
                    ->whereJsonContains('groupes_cibles', (string) $user->id);
              })

              // Extranet (invités)
              ->orWhere(function ($q) use ($user) {
                  $q->where('visibilite', 'extranet')
                    ->when($user->mainRole()?->nom === 'invite', fn($sq) => $sq);
              })

              // Ancienne compatibilité : groupes_acces (à conserver ou migrer progressivement)
              ->orWhereJsonContains('groupes_acces', (string) $user->id);
        });
    }

    /**
     * Helper : Vérifie si l'événement est visible pour un utilisateur donné.
     * Très utile pour debug, policies, ou tests unitaires.
     */
    public function isVisibleForUser($user)
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($this->visibilite === 'global') return true;
        if ($this->visibilite === 'entite' && $this->entite_id === $user->entite_id) return true;
        if ($this->visibilite === 'directions' && in_array($user->direction_id, $this->directions_cibles ?? [])) return true;
        if ($this->visibilite === 'roles' && in_array($user->mainRole()?->nom, $this->roles_cibles ?? [])) return true;
        if ($this->visibilite === 'groupes' && in_array((string) $user->id, $this->groupes_cibles ?? [])) return true;

        // Ancienne compatibilité
        if (in_array((string) $user->id, $this->groupes_acces ?? [])) return true;

        return false;
    }
}