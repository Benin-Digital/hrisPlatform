<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable; // ✅ Ajout

class Candidature extends Model
{
    use Notifiable; //  Ajout

    protected $fillable = [
        'offre_emploi_id',
        'type',
        'nom',
        'prenom',
        'email',
        'telephone',
        'cv_path',
        'lettre_motivation',
        'statut',
        'date_entretien',
        'heure_entretien',
        'lieu_entretien',
        'notes_entretien',
        'evaluation',
        'score_technique',
        'score_comportemental',
        'recruteur_id',
        'commentaire_recruteur',
        'date_validation',
    ];

    protected $casts = [
        'date_entretien' => 'date',
        'date_validation' => 'datetime',
    ];

    // Relation avec l'offre d'emploi
    public function offre()
    {
        return $this->belongsTo(OffreEmploi::class, 'offre_emploi_id');
    }

    // Relation avec le recruteur
    public function recruteur()
    {
        return $this->belongsTo(Utilisateur::class, 'recruteur_id');
    }

    // Relation avec les entretiens
    public function entretiens()
    {
        return $this->hasMany(Entretien::class);
    }

    // Label du statut
    public function getStatutLabelAttribute()
    {
        return [
            'nouveau' => 'Nouveau',
            'en_cours' => 'En cours',
            'entretien_planifie' => 'Entretien planifié',
            'entretien_realise' => 'Entretien réalisé',
            'offre' => 'Offre en attente',
            'accepte' => 'Accepté',
            'refuse' => 'Refusé',
            'archive' => 'Archivé',
        ][$this->statut] ?? $this->statut;
    }

    // Score total (moyenne des scores technique et comportemental)
    public function getScoreTotalAttribute()
    {
        if ($this->score_technique && $this->score_comportemental) {
            return round(($this->score_technique + $this->score_comportemental) / 2, 1);
        }
        return null;
    }

    // Définir l'adresse email pour les notifications (obligatoire avec Notifiable)
    public function routeNotificationForMail()
    {
        return $this->email;
    }
}