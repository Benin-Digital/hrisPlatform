<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategorieFormation extends Model
{
    // IMPORTANT : Spécifier le nom exact de la table
    protected $table = 'categories_formations';

    protected $fillable = [
        'nom',
        'slug',
        'description',
        'icone',
        'couleur',
        'parent_id',
        'ordre',
    ];

    // Relation parent
    public function parent()
    {
        return $this->belongsTo(CategorieFormation::class, 'parent_id');
    }

    // Relation enfants
    public function enfants()
    {
        return $this->hasMany(CategorieFormation::class, 'parent_id')->orderBy('ordre');
    }

    // Formations de cette catégorie
    public function formations()
    {
        return $this->hasMany(Formation::class, 'categorie_id');
    }
}