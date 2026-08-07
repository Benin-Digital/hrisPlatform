<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'nom', 'nom_affichage', 'module', 'description', 'categorie'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relations
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission')
                    ->withPivot('accorde');
    }
}