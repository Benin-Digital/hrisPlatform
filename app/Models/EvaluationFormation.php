<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationFormation extends Model
{
    protected $fillable = ['formation_id', 'utilisateur_id', 'note', 'commentaire'];

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'utilisateur_id');
    }
}
