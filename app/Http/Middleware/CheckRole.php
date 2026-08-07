<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  // Accepte plusieurs rôles séparés par des virgules
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Si pas connecté → redirection login (Breeze le fait déjà via 'auth', mais au cas où)
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // Récupère la liste des noms de rôles de l'utilisateur
        $userRoles = $request->user()->roles->pluck('nom')->toArray();

        // Vérifie si au moins un des rôles demandés est présent
        foreach ($roles as $role) {
            foreach ($userRoles as $userRole) {
                if (strtolower($role) === strtolower($userRole)) {
                    return $next($request);
                }
            }
        }

        \Log::warning('CheckRole Access Denied:', [
            'user' => $request->user()->email,
            'required_roles' => $roles,
            'user_roles' => $userRoles
        ]);

        // Accès refusé
        abort(403, 'Accès non autorisé. Vous n\'avez pas les permissions nécessaires.');
    }
}