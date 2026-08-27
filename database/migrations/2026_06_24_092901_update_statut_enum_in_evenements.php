<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE evenements DROP CONSTRAINT IF EXISTS evenements_statut_check");
            DB::statement("ALTER TABLE evenements ALTER COLUMN statut TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE evenements ALTER COLUMN statut SET NOT NULL");
            DB::statement("ALTER TABLE evenements ALTER COLUMN statut SET DEFAULT 'planifie'");
            DB::statement("ALTER TABLE evenements ADD CONSTRAINT evenements_statut_check CHECK (statut IN ('planifie','confirme','annule','termine','brouillon','publie'))");
        } else {
            DB::statement("ALTER TABLE evenements MODIFY COLUMN statut ENUM('planifie','confirme','annule','termine','brouillon','publie') NOT NULL DEFAULT 'planifie'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("UPDATE evenements SET statut = 'planifie' WHERE statut = 'publie'");
            DB::statement("ALTER TABLE evenements DROP CONSTRAINT IF EXISTS evenements_statut_check");
            DB::statement("ALTER TABLE evenements ADD CONSTRAINT evenements_statut_check CHECK (statut IN ('planifie','confirme','annule','termine','brouillon'))");
        } else {
            DB::statement("ALTER TABLE evenements MODIFY COLUMN statut ENUM('planifie','confirme','annule','termine','brouillon') NOT NULL DEFAULT 'planifie'");
        }
    }
};