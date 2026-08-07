<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'nom', 'nom_affichage', 'description', 'niveau',
        'est_systeme', 'permissions_defaut'
    ];

    protected $casts = [
        'est_systeme' => 'boolean',
        'permissions_defaut' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function utilisateurs()
    {
        return $this->belongsToMany(Utilisateur::class, 'utilisateur_role');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
                    ->withPivot('accorde');
    }
}