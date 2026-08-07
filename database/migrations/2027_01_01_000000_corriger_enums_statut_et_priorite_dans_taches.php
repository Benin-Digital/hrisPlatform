<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Corrige l'enum priorite pour inclure basse, moyenne, haute
        DB::statement("ALTER TABLE taches MODIFY COLUMN priorite ENUM('basse', 'moyenne', 'haute') NOT NULL DEFAULT 'moyenne'");

        // Assure que l'enum statut est correct (au cas où)
        DB::statement("ALTER TABLE taches MODIFY COLUMN statut ENUM('en_attente', 'en_cours', 'terminee', 'annulee') NOT NULL DEFAULT 'en_attente'");

        // Nettoyage : corrige les valeurs invalides existantes
        DB::update("UPDATE taches SET priorite = 'moyenne' WHERE priorite NOT IN ('basse', 'moyenne', 'haute')");
        DB::update("UPDATE taches SET statut = 'en_attente' WHERE statut NOT IN ('en_attente', 'en_cours', 'terminee', 'annulee')");
    }

    public function down(): void
    {
        // Revert (attention : peut perdre des données)
        DB::update("UPDATE taches SET priorite = 'moyenne' WHERE priorite NOT IN ('basse', 'moyenne')");
        DB::update("UPDATE taches SET statut = 'en_cours' WHERE statut = 'en_attente'");

        DB::statement("ALTER TABLE taches MODIFY COLUMN priorite ENUM('basse', 'moyenne') NOT NULL DEFAULT 'moyenne'");
        DB::statement("ALTER TABLE taches MODIFY COLUMN statut ENUM('en_cours', 'terminee', 'annulee') NOT NULL DEFAULT 'en_cours'");
    }
};