<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DossierDocument extends Model
{
    protected $table = 'dossiers_documents';

    protected $fillable = [
        'uuid', 'nom', 'dossier_parent_id', 'entite_id', 'direction_id', 'creer_par',
        'visibilite', 'qui_peut_voir', 'qui_peut_ajouter', 'qui_peut_modifier', 'qui_peut_supprimer',
        'quota_mo', 'espace_utilise_mo', 'est_actif', 'est_archive', 'chemin_complet'
    ];

    protected $casts = [
        'qui_peut_voir' => 'array',
        'qui_peut_ajouter' => 'array',
        'qui_peut_modifier' => 'array',
        'qui_peut_supprimer' => 'array',
        'est_actif' => 'boolean',
        'est_archive' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(DossierDocument::class, 'dossier_parent_id');
    }

    public function enfants()
    {
        return $this->hasMany(DossierDocument::class, 'dossier_parent_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'dossier_id');
    }

    public function entite()
    {
        return $this->belongsTo(Entite::class);
    }

    public function createur()
    {
        return $this->belongsTo(Utilisateur::class, 'creer_par');
    }
}