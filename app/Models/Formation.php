<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Formation extends Model
{
    protected $table = 'formations';

    protected $fillable = [
        'uuid', 'titre', 'slug', 'sous_titre', 'description', 'objectifs', 'prerequis',
        'categorie_id', 'formateur_principal_id', 'entite_id',
        'niveau', 'duree_minutes', 'points_competences', 'cout', 'devise',
        'image_couverture', 'video_presentation', 'lien_session', 'fichiers_joints',
        'est_public', 'mode_acces', 'capacite_max', 'certificat_disponible', 'evaluation_obligatoire',
        'date_debut', 'date_fin', 'date_limite_inscription',
        'nombre_vues', 'nombre_inscrits', 'note_moyenne', 'nombre_evaluations',
        'statut', 'publie_at',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_limite_inscription' => 'date',
        'publie_at' => 'datetime',
        'certificat_disponible' => 'boolean',
        'evaluation_obligatoire' => 'boolean',
        'est_public' => 'boolean',
        'fichiers_joints' => 'array',
        'duree_minutes' => 'integer',
        'capacite_max' => 'integer',
    ];

    public function categorie()
    {
        return $this->belongsTo(CategorieFormation::class);
    }

    public function formateur()
    {
        return $this->belongsTo(Utilisateur::class, 'formateur_principal_id');
    }

    public function entite()
    {
        return $this->belongsTo(Entite::class);
    }

    public function sequences()
    {
        return $this->hasMany(SequenceFormation::class);
    }

    public function inscriptions()
    {
        return $this->hasMany(InscriptionFormation::class);
    }

    public function evaluations()
    {
        return $this->hasMany(EvaluationFormation::class);
    }

     public function scopeVisible($query)
    {
        $user = auth()->user();
        if (!$user) return $query->where('statut', 'publie');

        if ($user->hasRole('super_admin')) return $query->where('statut', '!=', 'archive');

        return $query->where('statut', 'publie')
            ->where(function($q) use ($user) {
                if ($user->hasRole('invite')) {
                    // Les invités voient les formations externes/mixtes
                    $q->whereIn('mode_acces', ['externe', 'mixte']);
                } else {
                    // Les internes voient les formations de leur entité ou les global (entite_id null pour global?)
                    // On assume formation a un entite_id
                    $q->where('entite_id', $user->entite_id)
                      ->orWhere(function($sq) use ($user) {
                          $sq->whereIn('mode_acces', ['interne', 'mixte'])
                             ->where('entite_id', $user->entite_id);
                      });
                }
            });
    }
}