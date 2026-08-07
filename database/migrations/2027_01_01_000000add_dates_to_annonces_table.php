<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            // Ajoute date_epingle_jusqua seulement si elle n'existe pas
            if (!Schema::hasColumn('annonces', 'date_epingle_jusqua')) {
                $table->date('date_epingle_jusqua')->nullable()->after('est_epingle');
            }

            // Ajoute date_expiration seulement si elle n'existe pas (déjà présente)
            if (!Schema::hasColumn('annonces', 'date_expiration')) {
                $table->date('date_expiration')->nullable()->after('date_publication');
            }
        });
    }

    public function down(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            if (Schema::hasColumn('annonces', 'date_epingle_jusqua')) {
                $table->dropColumn('date_epingle_jusqua');
            }
            if (Schema::hasColumn('annonces', 'date_expiration')) {
                $table->dropColumn('date_expiration');
            }
        });
    }
};