<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE evenements DROP CONSTRAINT IF EXISTS evenements_statut_check");
            DB::statement("ALTER TABLE evenements ALTER COLUMN statut TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE evenements ALTER COLUMN statut SET DEFAULT 'planifie'");
            DB::statement("ALTER TABLE evenements ADD CONSTRAINT evenements_statut_check CHECK (statut IN ('planifie', 'confirme', 'annule', 'termine', 'brouillon'))");
        } else {
            DB::statement("ALTER TABLE `evenements` MODIFY COLUMN `statut` ENUM('planifie', 'confirme', 'annule', 'termine', 'brouillon') DEFAULT 'planifie'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE evenements SET statut = 'planifie' WHERE statut = 'brouillon'");

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE evenements DROP CONSTRAINT IF EXISTS evenements_statut_check");
            DB::statement("ALTER TABLE evenements ADD CONSTRAINT evenements_statut_check CHECK (statut IN ('planifie', 'confirme', 'annule', 'termine'))");
        } else {
            DB::statement("ALTER TABLE `evenements` MODIFY COLUMN `statut` ENUM('planifie', 'confirme', 'annule', 'termine') DEFAULT 'planifie'");
        }
    }
};
