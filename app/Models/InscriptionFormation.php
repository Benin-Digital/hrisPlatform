<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InscriptionFormation extends Model
{
    protected $table = 'inscription_formations';

    protected $fillable = [
        'formation_id',
        'utilisateur_id',
        'statut',
        'termine_at',
        'progression_pourcentage',
    ];

    protected $casts = [
        'termine_at' => 'datetime',
        'progression_pourcentage' => 'integer',
    ];

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }
}
