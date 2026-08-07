<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriqueDocument extends Model
{
    protected $table = 'historique_documents';

    protected $fillable = [
        'document_id',
        'utilisateur_id',
        'action',
        'details',
        'ip_adresse',
        'user_agent'
    ];

    //  AJOUTE CETTE LIGNE OBLIGATOIRE
    public $timestamps = false;

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }
}