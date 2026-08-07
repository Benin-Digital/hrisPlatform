<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('candidatures', function (Blueprint $table) {
            // Ajout des champs pour le processus de recrutement
            $table->enum('type', ['emploi', 'stage', 'alternance'])->default('emploi')->after('offre_emploi_id');
            $table->enum('statut', ['nouveau', 'en_cours', 'entretien_planifie', 'entretien_realise', 'offre', 'accepte', 'refuse', 'archive'])->default('nouveau')->change();
            $table->date('date_entretien')->nullable()->after('statut');
            $table->time('heure_entretien')->nullable()->after('date_entretien');
            $table->string('lieu_entretien')->nullable()->after('heure_entretien');
            $table->text('notes_entretien')->nullable()->after('lieu_entretien');
            $table->text('evaluation')->nullable()->after('notes_entretien');
            $table->integer('score_technique')->nullable()->after('evaluation');
            $table->integer('score_comportemental')->nullable()->after('score_technique');
            $table->foreignId('recruteur_id')->nullable()->constrained('utilisateurs')->nullOnDelete()->after('score_comportemental');
            $table->text('commentaire_recruteur')->nullable()->after('recruteur_id');
            $table->timestamp('date_validation')->nullable()->after('commentaire_recruteur');
        });
    }

    public function down()
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->dropColumn([
                'type', 'date_entretien', 'heure_entretien', 'lieu_entretien',
                'notes_entretien', 'evaluation', 'score_technique',
                'score_comportemental', 'recruteur_id', 'commentaire_recruteur',
                'date_validation'
            ]);
        });
    }
};