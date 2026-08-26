<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use App\Models\OffreEmploi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CandidatureController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $offreId)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'cv' => 'required|file|mimes:pdf,doc,docx|max:2048',
            'lettre_motivation' => 'nullable|string',
        ]);

        $offre = OffreEmploi::findOrFail($offreId);

        //  Stockage en local (privé)
        $path = $request->file('cv')->store('cvs', 'local');

        Candidature::create([
            'offre_emploi_id' => $offre->id,
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'cv_path' => $path,
            'lettre_motivation' => $request->lettre_motivation,
            'statut' => 'nouveau',
        ]);

        return back()->with('success', 'Votre candidature a été envoyée avec succès !');
    }

    /**
     * Display a listing of the resource (Admin).
     */
    public function index()
    {
        $candidatures = Candidature::with('offre')->latest()->get();

        $offres = OffreEmploi::with(['candidatures' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->get()->map(function($offre) {
            return [
                'id' => $offre->id,
                'titre' => $offre->titre,
                'candidatures' => $offre->candidatures->map(function($cand) {
                    return [
                        'id' => $cand->id,
                        'nom' => $cand->nom,
                        'prenom' => $cand->prenom,
                        'email' => $cand->email,
                        'statut' => $cand->statut,
                        'created_at' => $cand->created_at,
                    ];
                })
            ];
        });

        return Inertia::render('SuperAdmin/Candidatures/Index', [
            'candidatures' => $candidatures,
            'offres' => $offres,
        ]);
    }

    public function downloadCv(Candidature $candidature)
    {
        //  Vérifier sur le disque local
        if (!Storage::disk('local')->exists($candidature->cv_path)) {
            abort(404, 'CV non trouvé.');
        }
        return Storage::disk('local')->download($candidature->cv_path, 'CV_' . $candidature->nom . '_' . $candidature->prenom . '.' . pathinfo($candidature->cv_path, PATHINFO_EXTENSION));
    }

    public function destroy(Candidature $candidature)
    {
        //  Supprimer du disque local
        if (Storage::disk('local')->exists($candidature->cv_path)) {
            Storage::disk('local')->delete($candidature->cv_path);
        }
        $candidature->delete();
        return back()->with('success', 'Candidature supprimée.');
    }

    public function updateEtape(Request $request, $id)
    {
        $candidature = Candidature::findOrFail($id);

        $validated = $request->validate([
            'statut' => 'required|in:reçue,examen,entretien,offre,accepté,refusé',
        ]);

        $candidature->update(['statut' => $validated['statut']]);

        return response()->json(['success' => true]);
    }

    public function createSpontanee()
    {
        return Inertia::render('Candidature/Spontanee');
    }

    public function storeSpontanee(Request $request)
    {
        $validated = $request->validate([
            'nom'               => 'required|string|max:255',
            'prenom'            => 'required|string|max:255',
            'email'             => 'required|email|max:255',
            'telephone'         => 'nullable|string|max:20',
            'cv'                => 'required|file|mimes:pdf|max:10240',
            'lettre_motivation' => 'nullable|string|max:2000',
            'type'              => 'required|in:emploi,stage,alternance,spontanee',
            'message'           => 'nullable|string|max:2000',
        ]);

        // Stockage en local (privé)
        $cvPath = $request->file('cv')->store('candidatures/cv', 'local');

        Candidature::create([
            'offre_emploi_id'   => null,
            'type'              => $validated['type'],
            'nom'               => $validated['nom'],
            'prenom'            => $validated['prenom'],
            'email'             => $validated['email'],
            'telephone'         => $validated['telephone'],
            'cv_path'           => $cvPath,
            'lettre_motivation' => $validated['lettre_motivation'],
            'statut'            => 'nouveau',
            'commentaire_recruteur' => $validated['message'] ?? null,
        ]);

        return redirect()->route('candidatures.spontanee.create')
            ->with('success', 'Votre candidature a bien été envoyée. Nous vous recontacterons dès que possible.');
    }
}