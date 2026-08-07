<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Utilisateur;
use App\Models\Role;


class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
  public function store(Request $request): RedirectResponse
{
    $request->validate([
        'prenom' => ['required', 'string', 'max:100'],
        'nom' => ['required', 'string', 'max:100'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:utilisateurs'],
        'matricule' => ['nullable', 'string', 'max:50', 'unique:utilisateurs'],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $user = Utilisateur::create([
        'prenom' => $request->prenom,
        'nom' => $request->nom,
        'email' => $request->email,
        'matricule' => $request->matricule ?? 'MAT' . str_pad(Utilisateur::max('id') + 1, 6, '0', STR_PAD_LEFT), // auto-génère si vide
        'mot_de_passe' => Hash::make($request->password),
        'entite_id' => 1, // Entité par défaut (Entreprise Principale)
        'statut' => 'actif',
        'type_contrat' => 'CDI',
        'langue' => 'fr',
    ]);

    // Optionnel : attribuer rôle collaborateur par défaut
    $roleCollaborateur = Role::where('nom', 'collaborateur')->first();
    if ($roleCollaborateur) {
        $user->roles()->attach($roleCollaborateur);
    }

    event(new Registered($user));

    Auth::login($user);

    return redirect()->route('dashboard');
}
}
