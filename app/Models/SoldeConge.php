<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoldeConge extends Model
{
    protected $table = 'soldes_conges';
    
    protected $fillable = [
        'utilisateur_id',
        'type_conge',
        'annee',
        'solde_initial',
        'solde_pris',
        'solde_restant',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

    /**
     * Met à jour le solde restant après chaque validation de congé
     */
    public function decrementerSolde($duree)
    {
        $this->solde_pris += $duree;
        $this->solde_restant = $this->solde_initial - $this->solde_pris;
        $this->save();
    }

    /**
     * Vérifie si l'utilisateur a assez de jours pour le type de congé demandé
     */
    public function aAssezDeJours($duree)
    {
        return $this->solde_restant >= $duree;
    }
}