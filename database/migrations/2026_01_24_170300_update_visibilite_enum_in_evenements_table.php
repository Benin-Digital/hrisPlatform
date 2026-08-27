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
        DB::statement("UPDATE evenements SET visibilite = 'global' WHERE visibilite = 'public'");
        DB::statement("UPDATE evenements SET visibilite = 'entite' WHERE visibilite = 'prive'");
        DB::statement("UPDATE evenements SET visibilite = 'groupes' WHERE visibilite = 'groupe'");
        DB::statement("UPDATE evenements SET visibilite = 'directions' WHERE visibilite = 'direction'");

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE evenements DROP CONSTRAINT IF EXISTS evenements_visibilite_check");
            DB::statement("ALTER TABLE evenements ALTER COLUMN visibilite TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE evenements ALTER COLUMN visibilite SET DEFAULT 'entite'");
            DB::statement("ALTER TABLE evenements ADD CONSTRAINT evenements_visibilite_check CHECK (visibilite IN ('entite', 'global', 'roles', 'groupes', 'directions'))");
        } else {
            DB::statement("ALTER TABLE `evenements` MODIFY COLUMN `visibilite` ENUM('entite', 'global', 'roles', 'groupes', 'directions') DEFAULT 'entite'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE evenements SET visibilite = 'public' WHERE visibilite IN ('entite', 'global', 'roles', 'groupes', 'directions')");

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE evenements DROP CONSTRAINT IF EXISTS evenements_visibilite_check");
            DB::statement("ALTER TABLE evenements ALTER COLUMN visibilite SET DEFAULT 'public'");
            DB::statement("ALTER TABLE evenements ADD CONSTRAINT evenements_visibilite_check CHECK (visibilite IN ('public', 'prive', 'groupe', 'direction'))");
        } else {
            DB::statement("ALTER TABLE `evenements` MODIFY COLUMN `visibilite` ENUM('public', 'prive', 'groupe', 'direction') DEFAULT 'public'");
        }
    }
};
