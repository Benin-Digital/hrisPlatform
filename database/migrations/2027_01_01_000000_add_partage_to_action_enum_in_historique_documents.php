<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE historique_documents DROP CONSTRAINT IF EXISTS historique_documents_action_check");
            DB::statement("ALTER TABLE historique_documents ALTER COLUMN action TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE historique_documents ADD CONSTRAINT historique_documents_action_check CHECK (action IN ('creation', 'modification', 'telechargement', 'visualisation', 'suppression', 'restauration', 'verrouillage', 'deverrouillage', 'partage'))");
        } else {
            DB::statement("ALTER TABLE historique_documents MODIFY COLUMN action ENUM('creation', 'modification', 'telechargement', 'visualisation', 'suppression', 'restauration', 'verrouillage', 'deverrouillage', 'partage')");
        }
    }

    public function down(): void
    {
        DB::statement("UPDATE historique_documents SET action = 'creation' WHERE action = 'partage'");

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE historique_documents DROP CONSTRAINT IF EXISTS historique_documents_action_check");
            DB::statement("ALTER TABLE historique_documents ADD CONSTRAINT historique_documents_action_check CHECK (action IN ('creation', 'modification', 'telechargement', 'visualisation', 'suppression', 'restauration', 'verrouillage', 'deverrouillage'))");
        } else {
            DB::statement("ALTER TABLE historique_documents MODIFY COLUMN action ENUM('creation', 'modification', 'telechargement', 'visualisation', 'suppression', 'restauration', 'verrouillage', 'deverrouillage')");
        }
    }
};
