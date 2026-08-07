<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
    protected $table = 'directions';

    protected $fillable = [
        'nom', 'code_direction', 'entite_id', 'directeur_id',
        'direction_parent_id', 'description', 'budget_annuel'
    ];

    protected $casts = [
        'budget_annuel' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function entite()
    {
        return $this->belongsTo(Entite::class, 'entite_id');
    }

    public function directeur()
    {
        return $this->belongsTo(Utilisateur::class, 'directeur_id');
    }

    public function parent()
    {
        return $this->belongsTo(Direction::class, 'direction_parent_id');
    }

    public function enfants()
    {
        return $this->hasMany(Direction::class, 'direction_parent_id');
    }

    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class, 'direction_id');
    }
}