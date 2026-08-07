<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Assuming HasFactory is also needed as per the snippet

class OffreEmploi extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'titre',
        'description',
        'lieu',
        'type_contrat',
        'departement',
        'date_expiration',
        'is_published',
    ];

    protected $casts = [
        'date_expiration' => 'date',
        'is_published' => 'boolean',
    ];
}
