<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport RH</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            color: #333;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #aaa;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Rapport RH</h1>
        <p>Généré le {{ now()->format('d/m/Y H:i') }}</p>
        <p>Nombre total d'utilisateurs : {{ count($data) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Poste</th>
                <th>Entité</th>
                <th>Direction</th>
                <th>Statut</th>
                <th>Date embauche</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row['matricule'] }}</td>
                <td>{{ $row['nom'] }}</td>
                <td>{{ $row['prenom'] }}</td>
                <td>{{ $row['email'] }}</td>
                <td>{{ $row['poste'] }}</td>
                <td>{{ $row['entite_nom'] }}</td>
                <td>{{ $row['direction_nom'] }}</td>
                <td>{{ $row['statut'] }}</td>
                <td>{{ $row['date_embauche'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Généré par HRIS PRO • {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>