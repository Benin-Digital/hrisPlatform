<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tache extends Model
{
    protected $table = 'taches';

    protected $fillable = [
        'titre', 'description', 'entite_id', 'createur_id', 'projet_id', 'espace_id', 'assigne_a',
        'participants', 'date_debut', 'date_echeance', 'date_fin_reelle', 'priorite',
        'statut', 'progression_pourcentage', 'tags', 'estimation_heures',
        'temps_passe_minutes', 'fichiers_joints'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_echeance' => 'date',
        'date_fin_reelle' => 'date',
        'participants' => 'array',
        'tags' => 'array',
        'fichiers_joints' => 'array',
    ];

    // Relations
    public function espace()
    {
        return $this->belongsTo(EspaceCollaboratif::class, 'espace_id');
    }

    public function createur()
    {
        return $this->belongsTo(Utilisateur::class, 'createur_id');
    }

    public function assigne()
    {
        return $this->belongsTo(Utilisateur::class, 'assigne_a');
    }

    public function entite()
    {
        return $this->belongsTo(Entite::class, 'entite_id');
    }

    public function tempsSessions()
    {
        return $this->hasMany(TempsSession::class)->orderBy('created_at', 'desc');
    }

    public function tempsSessionEnCours()
    {
        return $this->hasOne(TempsSession::class)->where('est_en_cours', true);
    }
}
