<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Models\Entite;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\AnalysesRhExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AnalyseRhController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Requête de base pour les utilisateurs
        $query = Utilisateur::query();

        // Sécurité : On ne voit que son entité sauf si Super Admin
        if (!$user->hasRole('super_admin')) {
            if ($user->entite_id) {
                $query->where('entite_id', $user->entite_id);
            } else {
                // Si pas d'entité et pas super_admin (cas rare), on ne voit que soi
                $query->where('id', $user->id);
            }
        }

        // --- KPIs ---
        $totalUsers = (clone $query)->count();
        $activeUsers = (clone $query)->where('statut', 'actif')->count();
        $newUsersThisMonth = (clone $query)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        
        // --- Répartition par Statut ---
        $repartitionStatut = (clone $query)
            ->select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->get()
            ->pluck('total', 'statut');

        // --- Répartition par Type (Interne/Externe) ---
        $repartitionType = (clone $query)
            ->select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get()
            ->pluck('total', 'type');

        // --- Évolution des Recrutements (12 derniers mois) ---
        $evolutionRecrutement = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month = $date->format('m');
            $year = $date->format('Y');
            
            $count = (clone $query)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->count();

            $evolutionRecrutement[] = [
                'mois' => $date->translatedFormat('M Y'),
                'total' => $count
            ];
        }

        // --- Répartition par Entité (si Super Admin) ---
        $repartitionEntite = [];
        if ($user->hasRole('super_admin')) {
            $repartitionEntite = Entite::select('entites.id', 'entites.nom')
                ->leftJoin('utilisateurs', 'utilisateurs.entite_id', '=', 'entites.id')
                ->groupBy('entites.id', 'entites.nom')
                ->selectRaw('count(utilisateurs.id) as total')
                ->get();
        }

        // --- Dernières Recrues ---
        $dernieresRecrues = (clone $query)
            ->with('entite')
            ->latest()
            ->limit(8)
            ->get();

        return Inertia::render('RH/Analyses', [
            'stats' => [
                'total' => $totalUsers,
                'actifs' => $activeUsers,
                'nouveaux_mois' => $newUsersThisMonth,
                'taux_activite' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 1) : 0,
            ],
            'repartitionStatut' => $repartitionStatut,
            'repartitionType' => $repartitionType,
            'evolutionRecrutement' => $evolutionRecrutement,
            'repartitionEntite' => $repartitionEntite,
            'dernieresRecrues' => $dernieresRecrues,
        ]);
    }

    /**
     * Export CSV
     */
    public function exportCsv()
    {
        $data = $this->getAnalysesData();
        $filename = 'analyses_rh_' . date('Y-m-d') . '.csv';

        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // En-têtes
        fputcsv($handle, ['Matricule', 'Nom', 'Prénom', 'Email', 'Poste', 'Entité', 'Direction', 'Statut', 'Date d\'embauche']);

        foreach ($data as $row) {
            fputcsv($handle, [
                $row['matricule'],
                $row['nom'],
                $row['prenom'],
                $row['email'],
                $row['poste'],
                $row['entite_nom'],
                $row['direction_nom'],
                $row['statut'],
                $row['date_embauche'],
            ]);
        }

        fclose($handle);
        exit;
    }

    /**
     * Export PDF
     */
    public function exportPdf()
    {
        $data = $this->getAnalysesData();
        $pdf = Pdf::loadView('exports.analyses-rh-pdf', ['data' => $data]);
        return $pdf->download('analyses_rh_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export Excel
     */
    public function exportExcel()
    {
        $data = $this->getAnalysesData();
        return Excel::download(new AnalysesRhExport($data), 'analyses_rh_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Récupère les données pour les exports
     */
    private function getAnalysesData()
    {
        return Utilisateur::with(['entite', 'direction'])
            ->get()
            ->map(function ($user) {
                return [
                    'matricule' => $user->matricule ?? '',
                    'nom' => $user->nom ?? '',
                    'prenom' => $user->prenom ?? '',
                    'email' => $user->email ?? '',
                    'poste' => $user->poste ?? '',
                    'entite_nom' => $user->entite?->nom ?? '',
                    'direction_nom' => $user->direction?->nom ?? '',
                    'statut' => $user->statut ?? '',
                    'date_embauche' => $user->date_embauche ?? '',
                ];
            })
            ->toArray();
    }
}