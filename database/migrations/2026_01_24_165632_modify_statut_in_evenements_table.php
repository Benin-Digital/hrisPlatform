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
        // Using raw SQL to modify ENUM column to avoid Doctrine DBAL issues with ENUMs
        DB::statement("ALTER TABLE `evenements` MODIFY COLUMN `statut` ENUM('planifie', 'confirme', 'annule', 'termine', 'brouillon') DEFAULT 'planifie'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original ENUM definition
        DB::statement("UPDATE `evenements` SET `statut` = 'planifie' WHERE `statut` = 'brouillon'");
        DB::statement("ALTER TABLE `evenements` MODIFY COLUMN `statut` ENUM('planifie', 'confirme', 'annule', 'termine') DEFAULT 'planifie'");
    }
};
