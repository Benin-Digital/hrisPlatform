<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('taches', 'statut')) {
            DB::update("UPDATE taches SET statut = 'en_attente' WHERE statut NOT IN ('en_attente', 'en_cours', 'terminee', 'annulee')");

            if (DB::getDriverName() === 'pgsql') {
                DB::statement("ALTER TABLE taches DROP CONSTRAINT IF EXISTS taches_statut_check");
                DB::statement("ALTER TABLE taches ALTER COLUMN statut TYPE VARCHAR(255)");
                DB::statement("ALTER TABLE taches ALTER COLUMN statut SET NOT NULL");
                DB::statement("ALTER TABLE taches ALTER COLUMN statut SET DEFAULT 'en_attente'");
                DB::statement("ALTER TABLE taches ADD CONSTRAINT taches_statut_check CHECK (statut IN ('en_attente', 'en_cours', 'terminee', 'annulee'))");
            } else {
                DB::statement("ALTER TABLE taches MODIFY COLUMN statut ENUM('en_attente', 'en_cours', 'terminee', 'annulee') NOT NULL DEFAULT 'en_attente'");
            }
        } else {
            Schema::table('taches', function ($table) {
                $table->enum('statut', ['en_attente', 'en_cours', 'terminee', 'annulee'])
                      ->default('en_attente')
                      ->after('priorite');
            });
        }
    }

    public function down(): void
    {
        DB::update("UPDATE taches SET statut = 'en_cours' WHERE statut = 'en_attente'");

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE taches DROP CONSTRAINT IF EXISTS taches_statut_check");
            DB::statement("ALTER TABLE taches ALTER COLUMN statut SET DEFAULT 'en_cours'");
            DB::statement("ALTER TABLE taches ADD CONSTRAINT taches_statut_check CHECK (statut IN ('en_cours', 'terminee', 'annulee'))");
        } else {
            DB::statement("ALTER TABLE taches MODIFY COLUMN statut ENUM('en_cours', 'terminee', 'annulee') NOT NULL DEFAULT 'en_cours'");
        }
    }
};