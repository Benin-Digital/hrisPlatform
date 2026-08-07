<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OffreEmploi;
use Inertia\Inertia;

class OffreEmploiController extends Controller
{
    /**
     * Liste des offres d'emploi (Admin)
     */
    public function index()
    {
        $offres = OffreEmploi::latest()->get();
        return Inertia::render('SuperAdmin/Offres/Index', [
            'offres' => $offres
        ]);
    }

    /**
     * Formulaire creation
     */
    public function create()
    {
        return Inertia::render('SuperAdmin/Offres/Form');
    }

    /**
     * Store
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'lieu' => 'nullable|string|max:255',
            'type_contrat' => 'required|string',
            'departement' => 'nullable|string',
            'date_expiration' => 'nullable|date',
            'is_published' => 'boolean'
        ]);

        OffreEmploi::create($validated);
        return redirect()->route('super-admin.offres.index')
            ->with('success', 'Offre d\'emploi créée avec succès.');
    }

    /**
     * Edit
     */
    public function edit(OffreEmploi $offre)
    {
        return Inertia::render('SuperAdmin/Offres/Form', [
            'offre' => $offre
        ]);
    }

    /**
     * Update
     */
    public function update(Request $request, OffreEmploi $offre)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'lieu' => 'nullable|string|max:255',
            'type_contrat' => 'required|string',
            'departement' => 'nullable|string',
            'date_expiration' => 'nullable|date',
            'is_published' => 'boolean'
        ]);

        $offre->update($validated);
        return redirect()->route('super-admin.offres.index')
            ->with('success', 'Offre mise à jour.');
    }

    /**
     * Destroy
     */
    public function destroy(OffreEmploi $offre)
    {
        $offre->delete();
        return back()->with('success', 'Offre supprimée.');
    }

    /**
     * Toggle Publish
     */
    public function togglePublish(OffreEmploi $offre)
    {
        $offre->is_published = !$offre->is_published;
        $offre->save();
        return back()->with('success', $offre->is_published ? 'Offre publiée.' : 'Offre archivée.');
    }

    // ==================================================
    // MÉTHODES PUBLIQUES (sans authentification)
    // ==================================================

    /**
     * Liste publique des offres avec filtres
     */
    public function publicIndex(Request $request)
    {
        $query = OffreEmploi::where('is_published', true)
            ->where(function($q) {
                $q->whereNull('date_expiration')
                  ->orWhere('date_expiration', '>=', now());
            });

        // Application des filtres avec 'when' pour plus de clarté
        $query->when($request->filled('type'), function ($q) use ($request) {
            $q->where('type_contrat', $request->type);
        })
        ->when($request->filled('lieu'), function ($q) use ($request) {
            $q->where('lieu', 'LIKE', '%' . $request->lieu . '%');
        })
        ->when($request->filled('search'), function ($q) use ($request) {
            $q->where('titre', 'LIKE', '%' . $request->search . '%');
        });

        $offres = $query->latest()->paginate(12);

        return Inertia::render('Offres/PublicIndex', [
            'offres' => $offres,
            'filters' => $request->only(['type', 'lieu', 'search']),
        ]);
    }

    /**
     * Détail public d'une offre
     */
    public function publicShow($id)
    {
        $offre = OffreEmploi::where('is_published', true)
            ->where(function($q) {
                $q->whereNull('date_expiration')
                  ->orWhere('date_expiration', '>=', now());
            })
            ->findOrFail($id);

        return Inertia::render('Offres/PublicShow', [
            'offre' => $offre,
        ]);
    }
}