<?php

namespace App\Exports;

use App\Models\Pointage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;

class PointagesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $pointages;

    public function __construct($pointages)
    {
        $this->pointages = $pointages;
    }

    public function collection()
    {
        return $this->pointages;
    }

    public function headings(): array
    {
        return [
            '#',
            'Utilisateur',
            'Date',
            'Entrée',
            'Sortie',
            'Pause début',
            'Pause fin',
            'Temps travaillé',
            'Retard (min)',
            'Heures supp (min)',
            'Statut',
            'Validé',
        ];
    }

    public function map($pointage): array
    {
        return [
            $pointage->id,
            $pointage->utilisateur?->prenom . ' ' . $pointage->utilisateur?->nom,
            $pointage->date ? date('d/m/Y', strtotime($pointage->date)) : '-',
            $pointage->heure_entree ? substr($pointage->heure_entree, 0, 5) : '-',
            $pointage->heure_sortie ? substr($pointage->heure_sortie, 0, 5) : '-',
            $pointage->pause_debut ? substr($pointage->pause_debut, 0, 5) : '-',
            $pointage->pause_fin ? substr($pointage->pause_fin, 0, 5) : '-',
            $pointage->minutes_travaillees ? floor($pointage->minutes_travaillees / 60) . 'h' . ($pointage->minutes_travaillees % 60) . 'min' : '-',
            $pointage->minutes_retard ?? 0,
            $pointage->minutes_supplementaires ?? 0,
            $pointage->statut ?? '-',
            $pointage->valide ? 'Oui' : 'Non',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        return 'Pointages';
    }
}