<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('directions', function (Blueprint $table) {
            // Les colonnes entite_id, directeur_id, direction_parent_id existent déjà
            // On ajoute uniquement budget_annuel si elle est absente
            if (!Schema::hasColumn('directions', 'budget_annuel')) {
                $table->decimal('budget_annuel', 15, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('directions', function (Blueprint $table) {
            // On ne supprime que la colonne qu'on a éventuellement ajoutée
            if (Schema::hasColumn('directions', 'budget_annuel')) {
                $table->dropColumn('budget_annuel');
            }
        });
    }
};