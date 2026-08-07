<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AnalysesRhExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'Matricule', 'Nom', 'Prénom', 'Email', 'Poste',
            'Entité', 'Direction', 'Statut', "Date d'embauche"
        ];
    }

    public function map($row): array
    {
        return [
            $row['matricule'] ?? '',
            $row['nom'] ?? '',
            $row['prenom'] ?? '',
            $row['email'] ?? '',
            $row['poste'] ?? '',
            $row['entite_nom'] ?? '',
            $row['direction_nom'] ?? '',
            $row['statut'] ?? '',
            $row['date_embauche'] ?? '',
        ];
    }
}