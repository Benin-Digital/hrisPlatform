<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TempsSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'tache_id',
        'utilisateur_id',
        'debut',
        'fin',
        'duree_secondes',
        'est_en_cours',
    ];

    protected $casts = [
        'debut' => 'datetime',
        'fin' => 'datetime',
        'est_en_cours' => 'boolean',
    ];

    public function tache()
    {
        return $this->belongsTo(Tache::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }
}