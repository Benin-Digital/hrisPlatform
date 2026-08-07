<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;

$permissions = [
    ['nom' => 'access_internet',  'nom_affichage' => 'Accès Internet',    'module' => 'interfaces', 'categorie' => 'access', 'description' => 'Accès au site public'],
    ['nom' => 'access_extranet',  'nom_affichage' => 'Accès Extranet',    'module' => 'interfaces', 'categorie' => 'access', 'description' => 'Accès à l’extranet'],
    ['nom' => 'access_intranet',  'nom_affichage' => 'Accès Intranet',    'module' => 'interfaces', 'categorie' => 'access', 'description' => 'Accès à l’intranet'],
    ['nom' => 'create_profiles',  'nom_affichage' => 'Créer profils',     'module' => 'users',      'categorie' => 'users',  'description' => 'Créer des utilisateurs'],
    ['nom' => 'manage_externes',  'nom_affichage' => 'Gérer externes',    'module' => 'users',      'categorie' => 'users',  'description' => 'Gérer les invités'],
];

echo "Seeding Permissions...\n";
foreach ($permissions as $p) {
    Permission::updateOrCreate(['nom' => $p['nom']], $p);
}

echo "Assigning Permissions to Roles...\n";

// Map slugs to permission requirement
$mapping = [
    'super_admin'    => ['access_intranet', 'access_extranet', 'create_profiles', 'manage_externes'],
    'admin_entite'   => ['access_intranet', 'access_extranet', 'create_profiles', 'manage_externes'],
    'responsable_rh' => ['access_intranet', 'access_extranet', 'manage_externes'],
    'manager'        => ['access_intranet', 'access_extranet'],
    'formateur'      => ['access_intranet'],
    'collaborateur'  => ['access_intranet'],
    'invite'         => ['access_extranet'],
];

foreach ($mapping as $roleSlug => $perms) {
    $role = Role::where('nom', $roleSlug)->first();
    if ($role) {
        echo "Assigning perms to: {$roleSlug}\n";
        foreach ($perms as $pNom) {
            $permission = Permission::where('nom', $pNom)->first();
            if ($permission) {
                RolePermission::updateOrCreate([
                    'role_id' => $role->id,
                    'permission_id' => $permission->id,
                ], ['accorde' => true]);
            }
        }
    }
}

echo "Done!\n";
