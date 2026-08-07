<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- ROLES ---\n";
foreach(\App\Models\Role::all() as $r) {
    echo "ID: {$r->id} | Slug: {$r->nom} | Name: {$r->nom_affichage}\n";
}

echo "\n--- ENTITIES ---\n";
foreach(\App\Models\Entite::all() as $e) {
    echo "ID: {$e->id} | Name: {$e->nom}\n";
}

echo "\n--- USERS (last 5) ---\n";
foreach(\App\Models\Utilisateur::orderBy('id', 'desc')->take(5)->get() as $u) {
    echo "ID: {$u->id} | Matricule: {$u->matricule} | Email: {$u->email} | Type: {$u->type}\n";
}
