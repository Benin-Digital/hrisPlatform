<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Override total pour super_admin
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        // Permissions granulaires (exemples)
        Gate::define('manage-annonces-global', function ($user) {
            return $user->roles->flatMap->permissions->contains('nom', 'manage-annonces-global');
        });

        Gate::define('manage-evenements-global', function ($user) {
            return $user->roles->flatMap->permissions->contains('nom', 'manage-evenements-global');
        });

        // Ajoute d'autres si besoin (ex: 'pin-global-content')
    }
}