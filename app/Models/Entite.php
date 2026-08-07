<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entite extends Model
{
    protected $table = 'entites';

    protected $fillable = [
        'nom', 'code_entite', 'description', 'adresse', 'telephone',
        'email', 'logo', 'couleur_theme', 'est_active'
    ];

    protected $casts = [
        'est_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class, 'entite_id');
    }

    public function directions()
    {
        return $this->hasMany(Direction::class, 'entite_id');
    }
}