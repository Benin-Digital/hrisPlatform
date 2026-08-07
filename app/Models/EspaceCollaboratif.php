<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EspaceCollaboratif extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'espaces_collaboratifs';

    protected $fillable = [
        'uuid',
        'nom',
        'description',
        'image_couverture',
        'createur_id',
        'entite_id',
        'est_prive',
        'statut',
    ];

    protected $casts = [
        'est_prive' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function createur()
    {
        return $this->belongsTo(Utilisateur::class, 'createur_id');
    }

    public function entite()
    {
        return $this->belongsTo(Entite::class);
    }

    public function membres()
    {
        return $this->belongsToMany(Utilisateur::class, 'espace_membres', 'espace_id', 'utilisateur_id')
                    ->withPivot('role', 'date_rejoint')
                    ->withTimestamps();
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'espace_id');
    }

    public function taches()
    {
        return $this->hasMany(Tache::class, 'espace_id');
    }
}
