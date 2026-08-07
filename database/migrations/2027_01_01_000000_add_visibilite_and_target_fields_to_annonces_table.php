<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
{
    Schema::table('annonces', function (Blueprint $table) {
        if (!Schema::hasColumn('annonces', 'visibilite')) {
            $table->enum('visibilite', ['entite', 'global', 'roles', 'groupes', 'directions'])
                  ->default('entite')
                  ->after('utilisateurs_cibles');
        }

        if (!Schema::hasColumn('annonces', 'roles_cibles')) {
            $table->json('roles_cibles')->nullable()->after('visibilite');
        }

        if (!Schema::hasColumn('annonces', 'groupes_cibles')) {
            $table->json('groupes_cibles')->nullable()->after('roles_cibles');
        }

        if (!Schema::hasColumn('annonces', 'directions_cibles')) {
            $table->json('directions_cibles')->nullable()->after('groupes_cibles');
        }
    });
}

    public function down(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            $table->dropColumn(['visibilite', 'roles_cibles', 'groupes_cibles', 'directions_cibles']);
        });
    }
};