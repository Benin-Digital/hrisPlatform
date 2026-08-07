<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['utilisateur_id', 'entite_id', 'destinataire_id', 'espace_id', 'contenu', 'parent_id'];

    public function espace()
    {
        return $this->belongsTo(EspaceCollaboratif::class, 'espace_id');
    }

    public function auteur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }

    public function destinataire()
    {
        return $this->belongsTo(Utilisateur::class, 'destinataire_id');
    }

    public function entite()
    {
        return $this->belongsTo(Entite::class);
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_id');
    }
}