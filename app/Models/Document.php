<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'documents';

    protected $fillable = [
        'uuid', 'nom_fichier', 'nom_original', 'extension', 'mime_type', 'taille_octets',
        'titre', 'description', 'mots_cles', 'auteur', 'langue',
        'dossier_id', 'chemin_storage', 'proprietaire_id', 'entite_id', 'direction_id', 'espace_id',
        'version_majeure', 'version_mineure', 'version_patch', 'document_parent_id',
        'qui_peut_voir', 'qui_peut_telecharger', 'qui_peut_modifier', 'qui_peut_supprimer',
        'date_expiration', 'est_archive', 'deleted_at'
    ];

    public function espace()
    {
        return $this->belongsTo(EspaceCollaboratif::class, 'espace_id');
    }

    protected $casts = [
        'taille_octets' => 'integer',
        'qui_peut_voir' => 'array',
        'qui_peut_telecharger' => 'array',
        'qui_peut_modifier' => 'array',
        'qui_peut_supprimer' => 'array',
        'date_expiration' => 'date',
        'est_archive' => 'boolean',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function dossier()
    {
        return $this->belongsTo(DossierDocument::class, 'dossier_id');
    }

    public function proprietaire()
    {
        return $this->belongsTo(Utilisateur::class, 'proprietaire_id');
    }

    public function entite()
    {
        return $this->belongsTo(Entite::class);
    }

    public function direction()
    {
        return $this->belongsTo(Direction::class);
    }

    public function partages()
    {
        return $this->hasMany(PartageDocument::class, 'document_id');
    }
}