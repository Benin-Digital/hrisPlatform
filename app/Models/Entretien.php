<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entretien extends Model
{
    protected $fillable = [
        'candidature_id', 'offre_emploi_id', 'recruteur_id',
        'date_entretien', 'heure_entretien', 'lieu',
        'type', 'statut', 'notes', 'score', 'commentaire'
    ];

    protected $casts = [
        'date_entretien' => 'date',
    ];

    public function candidature()
    {
        return $this->belongsTo(Candidature::class);
    }

    public function offre()
    {
        return $this->belongsTo(OffreEmploi::class, 'offre_emploi_id');
    }

    public function recruteur()
    {
        return $this->belongsTo(Utilisateur::class, 'recruteur_id');
    }
}