<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Rubrique extends Model
{
    protected $table = 'rubriques';

    protected $fillable = [
        'nom', 'slug', 'description', 'icone', 'couleur', 'ordre', 'est_actif'
    ];

    protected $casts = [
        'est_actif' => 'boolean',
        'ordre' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($rubrique) {
            if (empty($rubrique->slug)) {
                $rubrique->slug = Str::slug($rubrique->nom);
            }
        });
    }
}
