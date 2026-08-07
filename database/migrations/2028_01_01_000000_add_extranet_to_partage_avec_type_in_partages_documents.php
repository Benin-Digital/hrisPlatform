<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partages_documents', function (Blueprint $table) {
            // Change l'ENUM pour ajouter 'extranet'
            $table->enum('partage_avec_type', [
                'utilisateur', 'groupe', 'direction', 'entite', 'role', 'extranet'
            ])->change();
        });
    }

    public function down(): void
    {
        Schema::table('partages_documents', function (Blueprint $table) {
            // Reviens à l'ancien ENUM (sans 'extranet')
            $table->enum('partage_avec_type', [
                'utilisateur', 'groupe', 'direction', 'entite', 'role'
            ])->change();
        });
    }
};