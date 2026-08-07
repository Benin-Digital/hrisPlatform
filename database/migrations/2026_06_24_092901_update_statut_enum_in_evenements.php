<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE evenements MODIFY COLUMN statut ENUM('planifie','confirme','annule','termine','brouillon','publie') NOT NULL DEFAULT 'planifie'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE evenements MODIFY COLUMN statut ENUM('planifie','confirme','annule','termine','brouillon') NOT NULL DEFAULT 'planifie'");
    }
};