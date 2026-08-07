<?php

namespace App\Http\Controllers;

use App\Models\StatistiquePublique;
use App\Models\Tache;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class StatistiquePubliqueController extends Controller
{
    /**
     * Liste des statistiques publiques (super admin)
     */
    public function index()
    {
        $statistiques = StatistiquePublique::orderBy('ordre')->get();

        return Inertia::render('SuperAdmin/StatistiquesPubliques/Index', [
            'statistiques' => $statistiques
        ]);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return Inertia::render('SuperAdmin/StatistiquesPubliques/Form', [
            'statistique' => null
        ]);
    }

    /**
     * Enregistrer une nouvelle statistique
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'data' => 'required|array',
            'is_published' => 'boolean',
            'ordre' => 'integer|min:0'
        ]);

        StatistiquePublique::create($validated);

        return redirect()->route('super-admin.statistiques-publiques.index')
            ->with('success', 'Statistique créée avec succès.');
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $statistique = StatistiquePublique::findOrFail($id);

        return Inertia::render('SuperAdmin/StatistiquesPubliques/Form', [
            'statistique' => $statistique
        ]);
    }

    /**
     * Mettre à jour une statistique
     */
    public function update(Request $request, $id)
    {
        $statistique = StatistiquePublique::findOrFail($id);

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'data' => 'required|array',
            'is_published' => 'boolean',
            'ordre' => 'integer|min:0'
        ]);

        $statistique->update($validated);

        return redirect()->route('super-admin.statistiques-publiques.index')
            ->with('success', 'Statistique mise à jour avec succès.');
    }

    /**
     * Supprimer une statistique
     */
    public function destroy($id)
    {
        $statistique = StatistiquePublique::findOrFail($id);
        $statistique->delete();

        return redirect()->route('super-admin.statistiques-publiques.index')
            ->with('success', 'Statistique supprimée avec succès.');
    }

    /**
     * Toggle publication
     */
    public function togglePublish($id)
    {
        $statistique = StatistiquePublique::findOrFail($id);
        $statistique->is_published = !$statistique->is_published;
        $statistique->save();

        return back()->with('success', 
            $statistique->is_published ? 'Statistique publiée.' : 'Statistique dépubliée.'
        );
    }

    /**
     * Générer automatiquement depuis les données de productivité
     */
    public function generateFromProductivite(Request $request)
    {
        // Récupérer les statistiques globales
        $totalTaches = Tache::count();
        $tachesTerminees = Tache::where('statut', 'terminee')->count();
        $tachesEnCours = Tache::where('statut', 'en_cours')->count();
        $tauxCompletion = $totalTaches > 0 ? round(($tachesTerminees / $totalTaches) * 100, 1) : 0;
        
        // Utilisateurs actifs (ayant au moins une tâche assignée)
        $utilisateursActifs = Tache::distinct('assigne_a')->count('assigne_a');

        // Créer les données
        $data = [
            'kpis' => [
                [
                    'label' => 'Tâches Totales',
                    'value' => $totalTaches,
                    'icon' => 'clipboard',
                    'color' => 'indigo'
                ],
                [
                    'label' => 'Taux de Complétion',
                    'value' => $tauxCompletion . '%',
                    'icon' => 'check-circle',
                    'color' => 'green'
                ],
                [
                    'label' => 'Tâches Terminées',
                    'value' => $tachesTerminees,
                    'icon' => 'target',
                    'color' => 'emerald'
                ],
                [
                    'label' => 'Utilisateurs Actifs',
                    'value' => $utilisateursActifs,
                    'icon' => 'users',
                    'color' => 'blue'
                ]
            ],
            'generated_at' => now()->format('Y-m-d H:i:s')
        ];

        // Créer la statistique
        $statistique = StatistiquePublique::create([
            'titre' => 'Performance ' . now()->format('F Y'),
            'data' => $data,
            'is_published' => false,
            'ordre' => StatistiquePublique::max('ordre') + 1
        ]);

        return redirect()->route('super-admin.statistiques-publiques.edit', $statistique->id)
            ->with('success', 'Statistique générée avec succès. Vous pouvez la modifier avant de la publier.');
    }

    /**
     * Réorganiser l'ordre
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:statistiques_publiques,id',
            'items.*.ordre' => 'required|integer|min:0'
        ]);

        foreach ($validated['items'] as $item) {
            StatistiquePublique::where('id', $item['id'])
                ->update(['ordre' => $item['ordre']]);
        }

        return back()->with('success', 'Ordre mis à jour avec succès.');
    }
}
