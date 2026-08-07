<?php

namespace App\Http\Controllers;

use App\Models\Pointage;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\PointagesExport;
use Maatwebsite\Excel\Facades\Excel;

class PointageController extends Controller
{
    /**
     * Liste des pointages (selon le rôle)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Pointage::with('utilisateur');

        if (!$user->hasRole('super_admin')) {
            $query->whereHas('utilisateur', function ($q) use ($user) {
                $q->where('entite_id', $user->entite_id);
            });
        }

        // Filtres
        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }
        if ($request->filled('utilisateur_id')) {
            $query->where('utilisateur_id', $request->utilisateur_id);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $pointages = $query->orderBy('date', 'desc')->orderBy('heure_entree', 'desc')->paginate(20);

        $utilisateurs = Utilisateur::select('id', 'prenom', 'nom')
            ->when(!$user->hasRole('super_admin'), function ($q) use ($user) {
                $q->where('entite_id', $user->entite_id);
            })
            ->orderBy('nom')
            ->get();

        // Récupérer le pointage du jour pour le BadgeuseButton
        $pointageDuJour = Pointage::where('utilisateur_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();

        return Inertia::render('Pointage/Index', [
            'pointages' => $pointages,
            'utilisateurs' => $utilisateurs,
            'filters' => $request->only(['date', 'utilisateur_id', 'statut']),
            'pointage_du_jour' => $pointageDuJour,
        ]);
    }

    /**
     * Badgeuse – Enregistrer l'entrée ou la sortie (retourne du JSON pour axios)
     */
    public function badgeuse(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $pointage = Pointage::where('utilisateur_id', $user->id)
            ->where('date', $today)
            ->first();

        // Si aucun pointage aujourd'hui, on crée une ligne
        if (!$pointage) {
            $pointage = Pointage::create([
                'utilisateur_id' => $user->id,
                'date' => $today,
            ]);
        }

        $action = $request->input('action');

        switch ($action) {
            case 'arrivee':
                if ($pointage->hasArrive()) {
                    return response()->json(['message' => 'Vous avez déjà pointé votre arrivée.'], 422);
                }
                $heure = now()->toTimeString();
                $pointage->heure_entree = $heure;
                $pointage->minutes_retard = $pointage->calculerRetard();
                $pointage->statut = ($pointage->minutes_retard > 0) ? 'retard' : 'present';
                $pointage->save();
                return response()->json(['message' => " Arrivée enregistrée à " . now()->format('H:i')]);

            case 'pause_debut':
                if (!$pointage->hasArrive()) {
                    return response()->json(['message' => "Vous devez d\'abord pointer votre arrivée."], 422);
                }
                if ($pointage->hasStartPause()) {
                    return response()->json(['message' => 'Vous êtes déjà en pause.'], 422);
                }
                $pointage->pause_debut = now()->toTimeString();
                $pointage->save();
                return response()->json(['message' => "⏸ Début de pause à " . now()->format('H:i')]);

            case 'pause_fin':
                if (!$pointage->hasStartPause()) {
                    return response()->json(['message' => "Vous n'avez pas encore commencé votre pause."], 422);
                }
                if ($pointage->hasEndPause()) {
                    return response()->json(['message' => 'Vous avez déjà repris le travail.'], 422);
                }
                $pointage->pause_fin = now()->toTimeString();
                $pointage->save();
                return response()->json(['message' => "Fin de pause à " . now()->format('H:i')]);

            case 'sortie':
                if (!$pointage->hasArrive()) {
                    return response()->json(['message' => "Vous devez d'abord pointer votre arrivée."], 422);
                }
                if ($pointage->hasStartPause() && !$pointage->hasEndPause()) {
                    return response()->json(['message' => "Vous devez d'abord terminer votre pause."], 422);
                }
                if ($pointage->hasSortie()) {
                    return response()->json(['message' => 'Vous avez déjà pointé votre sortie.'], 422);
                }
                $pointage->heure_sortie = now()->toTimeString();
                $pointage->minutes_supplementaires = $pointage->calculerHeuresSupp();
                $pointage->minutes_travaillees = $pointage->calculerTempsTravaille();
                $pointage->save();

                $msg = "⏱️ Sortie enregistrée à " . now()->format('H:i') .
                    ". Total travaillé : " . floor($pointage->minutes_travaillees / 60) . "h" . ($pointage->minutes_travaillees % 60) . "min";
                if ($pointage->minutes_supplementaires > 0) {
                    $msg .= " (Heures supp : " . floor($pointage->minutes_supplementaires / 60) . "h" . ($pointage->minutes_supplementaires % 60) . "min)";
                }
                return response()->json(['message' => $msg]);

            default:
                return response()->json(['message' => 'Action non reconnue.'], 422);
        }
    }

    /**
     * Afficher les pointages d'un collaborateur
     */
    public function show($id)
    {
        $pointage = Pointage::with('utilisateur')->findOrFail($id);
        $user = Auth::user();

        if (!$user->hasRole('super_admin') && $pointage->utilisateur->entite_id !== $user->entite_id) {
            abort(403);
        }

        return Inertia::render('Pointage/Show', [
            'pointage' => $pointage,
        ]);
    }

    /**
     * Statistiques mensuelles (pour dashboard)
     */
    public function stats($userId = null)
    {
        $user = Auth::user();
        $userId = $userId ?? $user->id;

        $mois = now()->month;
        $annee = now()->year;

        $stats = [
            'total' => Pointage::where('utilisateur_id', $userId)->whereMonth('date', $mois)->whereYear('date', $annee)->count(),
            'present' => Pointage::where('utilisateur_id', $userId)->whereMonth('date', $mois)->whereYear('date', $annee)->where('statut', 'present')->count(),
            'absent' => Pointage::where('utilisateur_id', $userId)->whereMonth('date', $mois)->whereYear('date', $annee)->where('statut', 'absent')->count(),
            'retard' => Pointage::where('utilisateur_id', $userId)->whereMonth('date', $mois)->whereYear('date', $annee)->where('statut', 'retard')->count(),
        ];

        return response()->json($stats);
    }

    public function validerJournee($id)
    {
        $pointage = Pointage::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['super_admin', 'responsable_rh', 'manager'])) {
            abort(403);
        }

        $pointage->update([
            'valide' => true,
            'valide_par' => $user->id,
            'valide_at' => now(),
        ]);

        return back()->with('success', 'Journée validée.');
    }

    public function exportPDF(Request $request)
    {
        $user = Auth::user();
        $query = Pointage::with('utilisateur');
        if(!$user->hasRole('super_admin')) {
            $query->whereHas('utilisateur', function ($q) use ($user) {
                $q->where('entite_id', $user->entite_id);
            });
        }
        //Appliquer les filtres 
        if($request->filled('date')) {
            $query->where('date', $request->date);
        }
        if($request->filled('utilisateur_id')) {
            $query->where('utilisateur_id', $request->utilisateur_id);
        }
        if($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        $pointages = $query->orderBy('date', 'desc')->get();
        $data = [
            'pointages' => $pointages,
            'date_export' => now()->format('d/m/Y H:i'),
            'nom_entite' => $user->entite->nom ?? 'Toutes les entités',
            'filters' => $request->only(['date', 'utilisateur_id', 'statut']),
        ];
        $pdf = Pdf::loadView('exports.pointages-pdf', $data);
        return $pdf->download('rapport_pointages_' . now()->format('Y-m-d_His') . '.pdf');
    }
    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        $query = Pointage::with('utilisateur');
        if(!$user->hasRole('super_admin')) {
            $query->whereHas('utilisateur', function ($q) use ($user) {
                $q->where('entite_id', $user->entite_id);
            });
        }
        //Appliquer les filtres 
        if($request->filled('date')) {
            $query->where('date', $request->date);
        }
        if($request->filled('utilisateur_id')) {
            $query->where('utilisateur_id', $request->utilisateur_id);
        }
        if($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        $pointages = $query->orderBy('date', 'desc')->get();
        return Excel::download(new PointagesExport($pointages), 'rapport_pointages_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    
}
