<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE partages_documents DROP CONSTRAINT IF EXISTS partages_documents_partage_avec_type_check");
            DB::statement("ALTER TABLE partages_documents ALTER COLUMN partage_avec_type TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE partages_documents ADD CONSTRAINT partages_documents_partage_avec_type_check CHECK (partage_avec_type IN ('utilisateur', 'groupe', 'direction', 'entite', 'role', 'extranet'))");
        } else {
            DB::statement("ALTER TABLE partages_documents MODIFY COLUMN partage_avec_type ENUM('utilisateur', 'groupe', 'direction', 'entite', 'role', 'extranet')");
        }
    }

    public function down(): void
    {
        DB::statement("UPDATE partages_documents SET partage_avec_type = 'utilisateur' WHERE partage_avec_type = 'extranet'");

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE partages_documents DROP CONSTRAINT IF EXISTS partages_documents_partage_avec_type_check");
            DB::statement("ALTER TABLE partages_documents ADD CONSTRAINT partages_documents_partage_avec_type_check CHECK (partage_avec_type IN ('utilisateur', 'groupe', 'direction', 'entite', 'role'))");
        } else {
            DB::statement("ALTER TABLE partages_documents MODIFY COLUMN partage_avec_type ENUM('utilisateur', 'groupe', 'direction', 'entite', 'role')");
        }
    }
};
