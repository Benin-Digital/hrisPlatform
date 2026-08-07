<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evenements', function (Blueprint $table) {
            // Visibilité principale
            if (!Schema::hasColumn('evenements', 'visibilite')) {
                $table->enum('visibilite', ['entite', 'global', 'roles', 'groupes', 'directions'])
                      ->default('entite')
                      ->after('groupes_acces');
            }

            // Champs JSON pour ciblage fin
            if (!Schema::hasColumn('evenements', 'roles_cibles')) {
                $table->json('roles_cibles')->nullable()->after('visibilite');
            }
            if (!Schema::hasColumn('evenements', 'groupes_cibles')) {
                $table->json('groupes_cibles')->nullable()->after('roles_cibles');
            }
            if (!Schema::hasColumn('evenements', 'directions_cibles')) {
                $table->json('directions_cibles')->nullable()->after('groupes_cibles');
            }

            // Épinglage global (priorité)
            if (!Schema::hasColumn('evenements', 'est_epingle')) {
                $table->boolean('est_epingle')->default(false)->after('directions_cibles');
            }
            if (!Schema::hasColumn('evenements', 'date_epingle_jusqua')) {
                $table->dateTime('date_epingle_jusqua')->nullable()->after('est_epingle');
            }
        });
    }

    public function down(): void
    {
        Schema::table('evenements', function (Blueprint $table) {
            $table->dropColumn([
                'visibilite', 'roles_cibles', 'groupes_cibles', 'directions_cibles',
                'est_epingle', 'date_epingle_jusqua'
            ]);
        });
    }
};