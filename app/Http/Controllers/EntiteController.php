<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Entite;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class EntiteController extends Controller
{
    public function index()
    {
        $entites = Entite::orderBy('nom')->paginate(15);

        return Inertia::render('SuperAdmin/Entites/Index', [
            'entites' => $entites,
        ]);
    }

    public function create()
    {
        return Inertia::render('SuperAdmin/Entites/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'            => 'required|string|max:255',
            'code_entite'    => 'required|string|max:50|unique:entites,code_entite',
            'description'    => 'nullable|string',
            'adresse'        => 'nullable|string',
            'telephone'      => 'nullable|string',
            'email'          => 'nullable|email',
            'logo'           => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'couleur_theme'  => 'nullable|string|max:50',
            'est_active'     => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $validated['est_active'] = $request->boolean('est_active', true);

        try {
            Entite::create($validated);
            return Redirect::route('super-admin.entites.index')
                ->with('success', 'Entité créée avec succès.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de l\'entité : ' . $e->getMessage());
        }
    }

    public function show(Entite $entite)
    {
        return Inertia::render('SuperAdmin/Entites/Show', [
            'entite' => $entite->load(['utilisateurs', 'directions']),
        ]);
    }

    public function edit(Entite $entite)
    {
        return Inertia::render('SuperAdmin/Entites/Edit', [
            'entite' => $entite,
        ]);
    }

    public function update(Request $request, Entite $entite)
    {
        $validated = $request->validate([
            'nom'            => 'required|string|max:255',
            'code_entite'    => 'required|string|max:50|unique:entites,code_entite,' . $entite->id,
            'description'    => 'nullable|string',
            'adresse'        => 'nullable|string',
            'telephone'      => 'nullable|string',
            'email'          => 'nullable|email',
            'logo'           => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'couleur_theme'  => 'nullable|string|max:50',
            'est_active'     => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo s'il existe
            if ($entite->logo) {
                Storage::disk('public')->delete($entite->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $validated['est_active'] = $request->boolean('est_active', $entite->est_active);

        try {
            $entite->update($validated);
            return Redirect::route('super-admin.entites.index')
                ->with('success', 'Entité mise à jour avec succès.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    public function destroy(Entite $entite)
    {
        try {
            // Optionnel : supprimer le logo avant suppression de l'entité
            if ($entite->logo) {
                Storage::disk('public')->delete($entite->logo);
            }

            $entite->delete();

            return Redirect::route('super-admin.entites.index')
                ->with('success', 'Entité supprimée avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
}