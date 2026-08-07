<?php

namespace App\Http\Controllers;

use App\Models\Pointage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ExportPointageController extends Controller
{
    public function exportPDF(Request $request)
    {
        $user = Auth::user();

        $query = Pointage::with('utilisateur');

        // Filtres (identiques à l'index)
        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }
        if ($request->filled('utilisateur_id')) {
            $query->where('utilisateur_id', $request->utilisateur_id);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Restriction par entité (si non super admin)
        if (!$user->hasRole('super_admin')) {
            $query->whereHas('utilisateur', function ($q) use ($user) {
                $q->where('entite_id', $user->entite_id);
            });
        }

        $pointages = $query->orderBy('date', 'desc')->get();

        $data = [
            'pointages' => $pointages,
            'date_export' => Carbon::now()->format('d/m/Y H:i'),
            'filtres' => $request->only(['date', 'utilisateur_id', 'statut']),
            'nom_entite' => $user->entite?->nom ?? 'Toutes les entités',
        ];

        $pdf = Pdf::loadView('exports.pointages-pdf', $data);
        return $pdf->download('pointages_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }
}