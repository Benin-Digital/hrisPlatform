<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatistiquePublique extends Model
{
    use HasFactory;

    protected $table = 'statistiques_publiques';

    protected $fillable = [
        'titre',
        'data',
        'is_published',
        'ordre'
    ];

    protected $casts = [
        'data' => 'array',
        'is_published' => 'boolean',
    ];

    /**
     * Scope pour récupérer uniquement les statistiques publiées
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)->orderBy('ordre');
    }
}
