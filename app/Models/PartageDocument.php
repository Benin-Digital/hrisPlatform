<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartageDocument extends Model
{
    protected $table = 'partages_documents';

    protected $fillable = [
        'document_id',
        'partage_avec_type',
        'partage_avec_id',
        'partage_par',
        'permissions'
    ];

    public $timestamps = true; // ou false si tu n’as pas created_at/updated_at

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function partageur()
    {
        return $this->belongsTo(Utilisateur::class, 'partage_par');
    }
}