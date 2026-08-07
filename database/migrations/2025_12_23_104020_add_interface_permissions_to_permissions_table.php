<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ajoute permissions si pas existantes
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->updateOrInsert(['nom' => 'access_internet'], [
                'nom_affichage' => 'Accès Internet',
                'module' => 'interfaces',
                'description' => 'Accès au site public/internet',
                'categorie' => 'access',
            ]);

            DB::table('permissions')->updateOrInsert(['nom' => 'access_extranet'], [
                'nom_affichage' => 'Accès Extranet',
                'module' => 'interfaces',
                'description' => 'Accès à l’extranet (clients/externes)',
                'categorie' => 'access',
            ]);

            DB::table('permissions')->updateOrInsert(['nom' => 'access_intranet'], [
                'nom_affichage' => 'Accès Intranet',
                'module' => 'interfaces',
                'description' => 'Accès à l’intranet interne',
                'categorie' => 'access',
            ]);

            DB::table('permissions')->updateOrInsert(['nom' => 'create_profiles'], [
                'nom_affichage' => 'Créer profils',
                'module' => 'users',
                'description' => 'Créer tous types de profils (internes/externes)',
                'categorie' => 'users',
            ]);

            DB::table('permissions')->updateOrInsert(['nom' => 'manage_externes'], [
                'nom_affichage' => 'Gérer externes/clients',
                'module' => 'users',
                'description' => 'Gérer collaborateurs externes/clients',
                'categorie' => 'users',
            ]);
        }
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('nom', ['access_internet', 'access_extranet', 'access_intranet', 'create_profiles', 'manage_externes'])->delete();
    }
};