<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pointages', function (Blueprint $table) {
            // Gestion de la pause
            $table->time('pause_debut')->nullable()->after('heure_sortie');
            $table->time('pause_fin')->nullable()->after('pause_debut');

            // Calcul automatique
            $table->integer('minutes_retard')->default(0)->after('statut');
            $table->integer('minutes_supplementaires')->default(0)->after('minutes_retard');
            $table->integer('minutes_travaillees')->default(0)->after('minutes_supplementaires');
        });
    }

    public function down()
    {
        Schema::table('pointages', function (Blueprint $table) {
            $table->dropColumn(['pause_debut', 'pause_fin', 'minutes_retard', 'minutes_supplementaires', 'minutes_travaillees']);
        });
    }
};