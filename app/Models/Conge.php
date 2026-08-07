<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conge extends Model
{
    use HasFactory;

    protected $fillable = [
        'utilisateur_id',
        'type_conge',
        'date_debut',
        'date_fin',
        'duree_ouvrable',
        'motif',
        'statut',
        'valide_par',
        'date_validation',
        'commentaire_validation',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_validation' => 'datetime',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

    public function validateur()
    {
        return $this->belongsTo(Utilisateur::class, 'valide_par');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeValides($query)
    {
        return $query->where('statut', 'valide');
    }
}