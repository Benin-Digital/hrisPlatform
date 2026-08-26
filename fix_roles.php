<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;

$rolesData = [
    'super_admin'    => ['name' => 'Super Admin',    'level' => 1],
    'admin_entite'   => ['name' => 'Admin Entité',  'level' => 2],
    'responsable_rh' => ['name' => 'Responsable RH', 'level' => 3],
    'manager'        => ['name' => 'Manager',        'level' => 4],
    'formateur'      => ['name' => 'Formateur',      'level' => 5],
    'collaborateur'  => ['name' => 'Collaborateur',  'level' => 6],
    'invite'         => ['name' => 'Invité / Partenaire', 'level' => 10],
];

echo "Standardizing Roles...\n";

foreach ($rolesData as $slug => $info) {
    // Tenter de trouver le rôle existant par nom d'affichage ou slug précédent
    $role = null;
    
    if ($slug === 'manager') {
        $role = Role::where('nom', 'Manage')->first();
    } elseif ($slug === 'admin_entite') {
        $role = Role::where('nom', 'AE')->first();
    }
    
    if (!$role) {
        $role = Role::where('nom', $slug)->first();
    }
    
    if ($role) {
        echo "Updating role: {$role->nom} -> {$slug}\n";
        $role->update([
            'nom' => $slug,
            'nom_affichage' => $info['name'],
            'niveau' => $info['level']
        ]);
    } else {
        echo "Creating role: {$slug}\n";
        Role::create([
            'nom' => $slug,
            'nom_affichage' => $info['name'],
            'niveau' => $info['level'],
            'est_systeme' => false
        ]);
    }
}

echo "Done!\n";
