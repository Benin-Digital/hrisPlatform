<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport des pointages</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header { text-align: center; margin-bottom: 20px; }
        .filters { margin-bottom: 15px; color: #555; }
        .footer { margin-top: 30px; font-size: 10px; color: #aaa; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport des pointages</h1>
        <p>Exporté le {{ $date_export }}</p>
        <p>Entité : {{ $nom_entite }}</p>
    </div>

    <div class="filters">
        @if($filtres['date'] ?? null)
            <strong>Date :</strong> {{ \Carbon\Carbon::parse($filtres['date'])->format('d/m/Y') }}<br>
        @endif
        @if($filtres['statut'] ?? null)
            <strong>Statut :</strong> {{ ucfirst($filtres['statut']) }}<br>
        @endif
        @if($filtres['utilisateur_id'] ?? null)
            @php $u = \App\Models\Utilisateur::find($filtres['utilisateur_id']); @endphp
            <strong>Utilisateur :</strong> {{ $u?->prenom }} {{ $u?->nom }}<br>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Utilisateur</th>
                <th>Date</th>
                <th>Entrée</th>
                <th>Sortie</th>
                <th>Pause début</th>
                <th>Pause fin</th>
                <th>Temps travaillé</th>
                <th>Retard (min)</th>
                <th>Heures supp (min)</th>
                <th>Statut</th>
                <th>Validé</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pointages as $index => $p)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $p->utilisateur?->prenom }} {{ $p->utilisateur?->nom }}</td>
                <td>{{ \Carbon\Carbon::parse($p->date)->format('d/m/Y') }}</td>
                <td>{{ $p->heure_entree ? substr($p->heure_entree, 0, 5) : '-' }}</td>
                <td>{{ $p->heure_sortie ? substr($p->heure_sortie, 0, 5) : '-' }}</td>
                <td>{{ $p->pause_debut ? substr($p->pause_debut, 0, 5) : '-' }}</td>
                <td>{{ $p->pause_fin ? substr($p->pause_fin, 0, 5) : '-' }}</td>
                <td>{{ $p->minutes_travaillees ? floor($p->minutes_travaillees / 60) . 'h' . ($p->minutes_travaillees % 60) . 'min' : '-' }}</td>
                <td>{{ $p->minutes_retard ?? 0 }}</td>
                <td>{{ $p->minutes_supplementaires ?? 0 }}</td>
                <td>{{ $p->statut }}</td>
                <td>{{ $p->valide ? ' Oui' : ' Non' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Généré par HRIS PRO • {{ $date_export }}
    </div>
</body>
</html>