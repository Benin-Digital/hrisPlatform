<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EspaceMembre extends Model
{
    use HasFactory;

    protected $table = 'espace_membres';

    protected $fillable = [
        'espace_id',
        'utilisateur_id',
        'role',
        'date_rejoint',
    ];

    protected $casts = [
        'date_rejoint' => 'datetime',
    ];

    public function espace()
    {
        return $this->belongsTo(EspaceCollaboratif::class, 'espace_id');
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }
}
