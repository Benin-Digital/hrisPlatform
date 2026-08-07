<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilComplementaire extends Model
{
    protected $table = 'profils_complementaires';

    protected $fillable = [
        'utilisateur_id', 'situation_familiale', 'nombre_enfants',
        'personne_urgence_nom', 'personne_urgence_telephone', 'personne_urgence_lien',
        'niveau_etudes', 'diplome_principal', 'competences', 'certifications',
        'langues', 'interets', 'associations', 'numero_secu', 'iban', 'bic',
        'mutuelle', 'champs_personnalises'
    ];

    protected $casts = [
        'competences' => 'array',
        'certifications' => 'array',
        'langues' => 'array',
        'champs_personnalises' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }
}