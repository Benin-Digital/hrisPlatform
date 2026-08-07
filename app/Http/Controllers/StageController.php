<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use App\Notifications\CandidatureStatusNotification; // ✅ Importer la notification
use Illuminate\Http\Request;
use Inertia\Inertia;

class StageController extends Controller
{
    public function create()
    {
        return Inertia::render('Stage/Demande');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'               => 'required|string|max:255',
            'prenom'            => 'required|string|max:255',
            'email'             => 'required|email|max:255',
            'telephone'         => 'nullable|string|max:20',
            'cv'                => 'required|file|mimes:pdf|max:10240',
            'lettre_motivation' => 'nullable|string|max:2000',
            'periode_debut'     => 'nullable|date',
            'periode_fin'       => 'nullable|date|after_or_equal:periode_debut',
            'domaine'           => 'nullable|string|max:255',
            'message'           => 'nullable|string|max:2000',
        ]);

        $cvPath = $request->file('cv')->store('candidatures/cv', 'public');

        // Enregistrement de la candidature
        $candidature = Candidature::create([
            'offre_emploi_id'   => null,
            'type'              => 'stage',
            'nom'               => $validated['nom'],
            'prenom'            => $validated['prenom'],
            'email'             => $validated['email'],
            'telephone'         => $validated['telephone'],
            'cv_path'           => $cvPath,
            'lettre_motivation' => $validated['lettre_motivation'],
            'statut'            => 'nouveau',
            'commentaire_recruteur' => $validated['message'] ?? null,
        ]);

        //  Envoi de l'email de confirmation au candidat
        $candidature->notify(new CandidatureStatusNotification(
            $candidature,
            'Nous avons bien reçu votre demande de stage. Nous vous recontacterons dès que possible.'
        ));

        return redirect()->route('stage.demande')
            ->with('success', 'Votre demande de stage a bien été envoyée. Vous recevrez un email de confirmation.');
    }
}