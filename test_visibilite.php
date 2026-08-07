&lt;?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app-&gt;make(Illuminate\Contracts\Console\Kernel::class);
$kernel-&gt;bootstrap();

use App\Models\Evenement;

try {
    $event = Evenement::create([
        'titre' =&gt; 'Test visibilité global',
        'description' =&gt; 'Test de correction ENUM',
        'date_debut' =&gt; '2026-02-01 10:00:00',
        'date_fin' =&gt; '2026-02-01 12:00:00',
        'fuseau_horaire' =&gt; 'Africa/Porto-Novo',
        'type_evenement' =&gt; 'formation',
        'type_lieu' =&gt; 'hybride',
        'organisateur_id' =&gt; 2,
        'entite_id' =&gt; 1,
        'statut' =&gt; 'brouillon',
        'visibilite' =&gt; 'global',
        'roles_cibles' =&gt; [],
        'groupes_cibles' =&gt; [],
        'directions_cibles' =&gt; [],
        'est_epingle' =&gt; true,
        'date_epingle_jusqua' =&gt; '2026-02-05 00:00:00'
    ]);
    
    echo "✅ SUCCESS! Événement créé avec l'ID: {$event-&gt;id}\n";
    echo "Visibilité: {$event-&gt;visibilite}\n";
    echo "Titre: {$event-&gt;titre}\n";
    
    // Cleanup
    $event-&gt;delete();
    echo "\n✅ Événement de test supprimé. Tout fonctionne correctement!\n";
    
} catch (\Exception $e) {
    echo "❌ ERREUR: " . $e-&gt;getMessage() . "\n";
    exit(1);
}
