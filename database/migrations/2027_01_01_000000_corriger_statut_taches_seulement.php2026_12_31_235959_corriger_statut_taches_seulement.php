<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // On vérifie d'abord si la colonne statut existe
        if (Schema::hasColumn('taches', 'statut')) {
            // On corrige l'enum pour inclure 'en_attente'
            DB::statement("ALTER TABLE taches MODIFY COLUMN statut ENUM('en_attente', 'en_cours', 'terminee', 'annulee') NOT NULL DEFAULT 'en_attente'");

            // On corrige les valeurs invalides existantes (sécurité)
            DB::update("UPDATE taches SET statut = 'en_attente' WHERE statut NOT IN ('en_attente', 'en_cours', 'terminee', 'annulee')");
        } else {
            // Si par miracle la colonne n'existe pas, on l'ajoute
            Schema::table('taches', function ($table) {
                $table->enum('statut', ['en_attente', 'en_cours', 'terminee', 'annulee'])
                      ->default('en_attente')
                      ->after('priorite');
            });
        }
    }

    public function down(): void
    {
        // Revert : on remet les anciennes valeurs possibles
        DB::update("UPDATE taches SET statut = 'en_cours' WHERE statut = 'en_attente'");
        DB::statement("ALTER TABLE taches MODIFY COLUMN statut ENUM('en_cours', 'terminee', 'annulee') NOT NULL DEFAULT 'en_cours'");
    }
};