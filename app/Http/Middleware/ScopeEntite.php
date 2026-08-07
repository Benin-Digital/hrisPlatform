<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ScopeEntite
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && !$user->hasRole('super_admin') && $user->entite_id) {
            // On peut ajouter des contraintes globales ici
            // Ou bien passer l'entite_id dans la request pour que les controllers l'utilisent
            $request->merge(['entite_id_filter' => $user->entite_id]);
        }

        return $next($request);
    }
}