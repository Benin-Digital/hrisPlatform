<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;

class Utilisateur extends Authenticatable implements CanResetPassword
{
    use Notifiable, CanResetPasswordTrait;

    protected $table = 'utilisateurs';

    protected $fillable = [
        'matricule', 'email', 'mot_de_passe', 'password', 'nom', 'prenom', 'civilite',
        'date_naissance', 'lieu_naissance', 'photo_profil', 'telephone',
        'telephone_urgence', 'adresse', 'ville', 'code_postal', 'pays',
        'entite_id', 'direction_id', 'poste', 'date_embauche', 'date_depart',
        'type_contrat', 'type', 'statut', 'langue', 'fuseau_horaire', 'theme',
        'notifications_email', 'notifications_push', 'deux_fa_active', 'dashboard_preference',
    ];

    protected $hidden = [
        'mot_de_passe', 'password', 'remember_token',
    ];

    protected $casts = [
        'email_verifie_at' => 'datetime',
        'date_naissance' => 'date',
        'date_embauche' => 'date',
        'date_depart' => 'date',
        'notifications_email' => 'boolean',
        'notifications_push' => 'boolean',
        'deux_fa_active' => 'boolean',
        'dernier_connexion_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // RELATIONS
    public function entite()
    {
        return $this->belongsTo(Entite::class, 'entite_id');
    }

    public function direction()
    {
        return $this->belongsTo(Direction::class, 'direction_id');
    }

    public function profilComplementaire()
    {
        return $this->hasOne(ProfilComplementaire::class, 'utilisateur_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'utilisateur_role')
                    ->withPivot('assigne_le', 'assigne_par');
    }

    public function espaces()
    {
        return $this->belongsToMany(EspaceCollaboratif::class, 'espace_membres', 'utilisateur_id', 'espace_id')
                    ->withPivot('role', 'date_rejoint')
                    ->withTimestamps();
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'utilisateur_permission')
                    ->withPivot('assigne_le', 'assigne_par')
                    ->withTimestamps();
    }

    // MÉTHODES RÔLES
    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains('nom', $roleName);
    }

    /**
     * Vérifie si l'utilisateur a une permission via ses rôles ou directement.
     */
    public function hasPermission(string $permissionName): bool
    {
        // 1. Permission directe
        if ($this->permissions->contains('nom', $permissionName)) {
            return true;
        }

        // 2. Via les rôles
        foreach ($this->roles as $role) {
            $permission = $role->permissions->firstWhere('nom', $permissionName);
            if ($permission && $permission->pivot->accorde) {
                return true;
            }
        }

        return false;
    }

    public function hasAnyRole($roles): bool
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        return $this->roles()->whereIn('nom', $roles)->exists();
    }

    public function mainRole()
    {
        return $this->roles->sortBy('niveau')->first();
    }

    // AUTH – CORRECTION CLÉ
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    // Permet à Laravel d'écrire dans mot_de_passe lors des updates
    public function setPasswordAttribute($value)
    {
        $this->attributes['mot_de_passe'] = $value;
    }

    public function getPrenomNomAttribute(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public function conges()
    {
        return $this->hasMany(Conge::class);
    }

    // Relations existantes...
    public function formations()
    {
        return $this->belongsToMany(Formation::class, 'inscription_formations', 'utilisateur_id', 'formation_id')
                    ->withPivot('statut', 'progression_pourcentage')
                    ->withTimestamps();
    }

    public function taches()
    {
        return $this->hasMany(Tache::class, 'assigne_a');
    }

    public function documentsProprietaire()
    {
        return $this->hasMany(Document::class, 'proprietaire_id');
    }

    public function historiqueDocuments()
    {
        return $this->hasMany(HistoriqueDocument::class, 'utilisateur_id');
    }

    public function soldesConges()
    {
        return $this->hasMany(SoldeConge::class);
    }

    /**
     * Vérifie si l'utilisateur est exempté de pointage pour une date donnée
     * (congé validé, jour férié, permission spéciale, etc.)
     *
     * @param string $date Date au format YYYY-MM-DD
     * @return bool
     */
    public function estExempte($date): bool
    {
        // 1. Vérifier si l'utilisateur a un congé validé pour cette date
        $conge = Conge::where('utilisateur_id', $this->id)
            ->where('statut', 'valide')
            ->where('date_debut', '<=', $date)
            ->where('date_fin', '>=', $date)
            ->exists();

        if ($conge) {
            return true;
        }

        // 2. (Optionnel) Vérifier les jours fériés
        // if (JourFerie::where('date', $date)->exists()) {
        //     return true;
        // }

        // 3. (Optionnel) Vérifier les autorisations spéciales (ex: permission de retard)
        // if (AutorisationRetard::where('utilisateur_id', $this->id)
        //     ->where('date', $date)
        //     ->exists()
        // ) {
        //     return true;
        // }

        return false;
    }
}