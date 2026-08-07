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
        // Step 1: Map existing old values to new values
        // public -> global (public visibility for all)
        // prive -> entite (private to entity)
        // groupe -> groupes (group visibility)
        // direction -> directions (direction visibility)
        DB::statement("UPDATE `evenements` SET `visibilite` = 'global' WHERE `visibilite` = 'public'");
        DB::statement("UPDATE `evenements` SET `visibilite` = 'entite' WHERE `visibilite` = 'prive'");
        DB::statement("UPDATE `evenements` SET `visibilite` = 'groupes' WHERE `visibilite` = 'groupe'");
        DB::statement("UPDATE `evenements` SET `visibilite` = 'directions' WHERE `visibilite` = 'direction'");
        
        // Step 2: Modify the enum to only include the new values
        DB::statement("ALTER TABLE `evenements` MODIFY COLUMN `visibilite` ENUM('entite', 'global', 'roles', 'groupes', 'directions') DEFAULT 'entite'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum (update values first to avoid data loss)
        DB::statement("UPDATE `evenements` SET `visibilite` = 'public' WHERE `visibilite` IN ('entite', 'global', 'roles', 'groupes', 'directions')");
        DB::statement("ALTER TABLE `evenements` MODIFY COLUMN `visibilite` ENUM('public', 'prive', 'groupe', 'direction') DEFAULT 'public'");
    }
};
