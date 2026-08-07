<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Pour la table annonces
        Schema::table('annonces', function (Blueprint $table) {
            if (!Schema::hasColumn('annonces', 'groupes_cibles')) {
                $table->json('groupes_cibles')->nullable();
            }
            if (!Schema::hasColumn('annonces', 'visibilite')) {
                $table->string('visibilite')->default('tous');
            }
        });

        // Pour la table evenements
        if (Schema::hasTable('evenements')) {
            Schema::table('evenements', function (Blueprint $table) {
                if (!Schema::hasColumn('evenements', 'groupes_cibles')) {
                    $table->json('groupes_cibles')->nullable();
                }
                if (!Schema::hasColumn('evenements', 'visibilite')) {
                    $table->string('visibilite')->default('tous');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            $table->dropColumn(['groupes_cibles', 'visibilite']);
        });

        if (Schema::hasTable('evenements')) {
            Schema::table('evenements', function (Blueprint $table) {
                $table->dropColumn(['groupes_cibles', 'visibilite']);
            });
        }
    }
};