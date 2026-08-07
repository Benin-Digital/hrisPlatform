<?php

namespace App\Http\Controllers;

use App\Models\Rubrique;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class RubriqueController extends Controller
{
    public function index()
    {
        $this->authorizeAdmin();
        
        return Inertia::render('Rubriques/Index', [
            'rubriques' => Rubrique::orderBy('ordre')->get()
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:rubriques,nom',
            'description' => 'nullable|string',
            'icone' => 'nullable|string',
            'couleur' => 'nullable|string',
            'ordre' => 'integer'
        ]);

        $validated['slug'] = Str::slug($validated['nom']);
        Rubrique::create($validated);

        return redirect()->back()->with('success', 'Rubrique créée avec succès.');
    }

    public function update(Request $request, Rubrique $rubrique)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:rubriques,nom,' . $rubrique->id,
            'description' => 'nullable|string',
            'icone' => 'nullable|string',
            'couleur' => 'nullable|string',
            'ordre' => 'integer',
            'est_actif' => 'boolean'
        ]);

        if ($request->has('nom')) {
            $validated['slug'] = Str::slug($validated['nom']);
        }

        $rubrique->update($validated);

        return redirect()->back()->with('success', 'Rubrique mise à jour.');
    }

    public function destroy(Rubrique $rubrique)
    {
        $this->authorizeAdmin();
        $rubrique->delete();

        return redirect()->back()->with('success', 'Rubrique supprimée.');
    }

    private function authorizeAdmin()
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['super_admin', 'admin_entite', 'responsable_rh', 'manager'])) {
            abort(403, 'Action non autorisée.');
        }
    }
}
