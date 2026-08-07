<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use App\Models\OffreEmploi;
use App\Models\Entretien;
use App\Models\Utilisateur;
use App\Notifications\CandidatureStatusNotification;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RecrutementController extends Controller
{
    /**
     * Pipeline de recrutement (Kanban)
     */
    public function pipeline(Request $request)
    {
        $user = Auth::user();

        $query = Candidature::with(['offre', 'recruteur']);

        if ($request->filled('offre_id')) {
            $query->where('offre_emploi_id', $request->offre_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Récupérer toutes les candidatures
        $candidatures = $query->orderBy('created_at', 'desc')->get();

        //  Groupement des candidatures de la page courante
        $grouped = $candidatures->groupBy('statut');

        // Totaux réels par statut (nombre d'éléments dans chaque groupe)
        $totals = $grouped->map->count()->toArray();

        $statuts = [
            'nouveau' => ['label' => ' Nouveau', 'color' => 'blue'],
            'en_cours' => ['label' => 'En cours', 'color' => 'yellow'],
            'entretien_planifie' => ['label' => ' Entretien planifié', 'color' => 'purple'],
            'entretien_realise' => ['label' => 'Entretien réalisé', 'color' => 'indigo'],
            'offre' => ['label' => ' Offre en attente', 'color' => 'orange'],
            'accepte' => ['label' => ' Accepté', 'color' => 'green'],
            'refuse' => ['label' => ' Refusé', 'color' => 'red'],
            'archive' => ['label' => ' Archivé', 'color' => 'gray'],
        ];

        $offres = OffreEmploi::orderBy('titre')->get(['id', 'titre']);

        return Inertia::render('Recrutement/Pipeline', [
            'candidatures' => $candidatures,
            'grouped'      => $grouped,
            'totals'       => $totals,    
            'statuts'      => $statuts,
            'offres'       => $offres,
            'filters'      => $request->only(['offre_id', 'type']),
        ]);
    }

    /**
     * Planifier un entretien
     */
    public function planifierEntretien(Request $request, $id)
    {
        Log::info('Planification entretien', ['candidature_id' => $id]);

        $candidature = Candidature::findOrFail($id);

        $validated = $request->validate([
            'date_entretien' => 'required|date|after:today',
            'heure_entretien' => 'required|date_format:H:i',
            'lieu_entretien' => 'nullable|string|max:255',
            'type' => 'required|in:presentiel,visio,telephonique',
            'recruteur_id' => 'required|exists:utilisateurs,id',
        ]);

        $candidature->update([
            'date_entretien' => $validated['date_entretien'],
            'heure_entretien' => $validated['heure_entretien'],
            'lieu_entretien' => $validated['lieu_entretien'],
            'recruteur_id' => $validated['recruteur_id'],
            'statut' => 'entretien_planifie',
        ]);

        Entretien::create([
            'candidature_id' => $candidature->id,
            'offre_emploi_id' => $candidature->offre_emploi_id,
            'recruteur_id' => $validated['recruteur_id'],
            'date_entretien' => $validated['date_entretien'],
            'heure_entretien' => $validated['heure_entretien'],
            'lieu' => $validated['lieu_entretien'],
            'type' => $validated['type'],
            'statut' => 'planifie',
        ]);

        $message = "Votre entretien est planifié le " . $validated['date_entretien'] . " à " . $validated['heure_entretien'] . ".";
        if ($validated['lieu_entretien']) {
            $message .= " Lieu : " . $validated['lieu_entretien'];
        }
        $candidature->notify(new CandidatureStatusNotification($candidature, $message));

        return back()->with('success', 'Entretien planifié et notification envoyée.');
    }

    /**
     * Afficher le formulaire de planification
     */
    public function showPlanifierEntretien($id)
    {
        $candidature = Candidature::with('offre')->findOrFail($id);
        $recruteurs = Utilisateur::whereHas('roles', function ($q) {
            $q->whereIn('nom', ['responsable_rh', 'super_admin']);
        })->get(['id', 'prenom', 'nom']);

        return Inertia::render('Recrutement/Planifier', [
            'candidature' => $candidature,
            'recruteurs' => $recruteurs,
        ]);
    }

    /**
     * Noter un entretien
     */
    public function noterEntretien(Request $request, $id)
    {
        Log::info('Notation entretien', ['candidature_id' => $id]);

        $candidature = Candidature::findOrFail($id);

        $validated = $request->validate([
            'score_technique' => 'required|integer|min:0|max:100',
            'score_comportemental' => 'required|integer|min:0|max:100',
            'evaluation' => 'nullable|string|max:2000',
            'commentaire_recruteur' => 'nullable|string|max:2000',
        ]);

        $candidature->update([
            'score_technique' => $validated['score_technique'],
            'score_comportemental' => $validated['score_comportemental'],
            'evaluation' => $validated['evaluation'],
            'commentaire_recruteur' => $validated['commentaire_recruteur'],
            'statut' => 'entretien_realise',
        ]);

        $entretien = $candidature->entretiens()->latest()->first();
        if ($entretien) {
            $entretien->update([
                'score' => round(($validated['score_technique'] + $validated['score_comportemental']) / 2, 1),
                'commentaire' => $validated['commentaire_recruteur'],
                'statut' => 'realise',
            ]);
        }

        $candidature->notify(new CandidatureStatusNotification(
            $candidature,
            "Votre entretien a été évalué. Nous vous donnerons une réponse dans les meilleurs délais."
        ));

        return back()->with('success', 'Entretien évalué et notification envoyée.');
    }

    /**
     * Changer le statut (Kanban drag & drop)
     */
    public function changeStatut(Request $request, $id)
    {
        Log::info('Changement statut', ['candidature_id' => $id, 'statut' => $request->statut]);

        $candidature = Candidature::findOrFail($id);

        $validated = $request->validate([
            'statut' => 'required|in:nouveau,en_cours,entretien_planifie,entretien_realise,offre,accepte,refuse,archive',
        ]);

        if ($validated['statut'] === 'accepte') {
            $validated['date_validation'] = now();
            $type = $candidature->type ?? 'candidature';
            $message = $type === 'stage'
                ? "Félicitations ! Votre candidature de stage a été acceptée. Nous vous contacterons pour les modalités."
                : "Félicitations ! Votre candidature a été acceptée. Nous vous contacterons pour la suite du processus.";
            $candidature->notify(new CandidatureStatusNotification($candidature, $message));
        }

        if ($validated['statut'] === 'offre') {
            $candidature->notify(new CandidatureStatusNotification(
                $candidature,
                "Vous êtes sélectionné(e) pour une offre. Nous vous contacterons sous peu."
            ));
        }

        $candidature->update($validated);

        return response()->json(['success' => true]);
    }

    /**
     * Valider une candidature (embauche)
     */
    public function valider($id)
    {
        Log::info('Validation candidature', ['candidature_id' => $id]);

        $candidature = Candidature::findOrFail($id);

        // Seule une candidature avec statut 'entretien_realise' ou 'offre' peut être validée
        if (!in_array($candidature->statut, ['entretien_realise', 'offre'])) {
            return back()->with('error', 'Cette candidature ne peut pas être validée pour l\'instant (statut actuel : ' . $candidature->statut . ').');
        }

        $candidature->update([
            'statut' => 'accepte',
            'date_validation' => now(),
        ]);

        $candidature->notify(new CandidatureStatusNotification(
            $candidature,
            "Félicitations ! Votre candidature a été acceptée. Nous vous contacterons pour la suite du processus."
        ));

        return back()->with('success', 'Candidature validée et embauche enregistrée.');
    }

    /**
     * Rejeter une candidature
     */
    public function rejeter(Request $request, $id)
    {
        Log::info('Rejet candidature', ['candidature_id' => $id]);

        $candidature = Candidature::findOrFail($id);

        $validated = $request->validate([
            'motif' => 'nullable|string|max:1000',
        ]);

        $candidature->update([
            'statut' => 'refuse',
            'commentaire_recruteur' => $validated['motif'] ?? null,
        ]);

        $message = "Nous vous remercions de votre candidature. Malheureusement, nous ne donnons pas suite à votre demande.";
        if (!empty($validated['motif'])) {
            $message .= " Motif : " . $validated['motif'];
        }
        $candidature->notify(new CandidatureStatusNotification($candidature, $message));

        return back()->with('success', 'Candidature rejetée et notification envoyée.');
    }
}