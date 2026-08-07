<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('entretiens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidature_id')->constrained('candidatures')->onDelete('cascade');
            $table->foreignId('offre_emploi_id')->nullable()->constrained('offre_emplois')->nullOnDelete();
            $table->foreignId('recruteur_id')->constrained('utilisateurs');
            $table->date('date_entretien');
            $table->time('heure_entretien');
            $table->string('lieu')->nullable();
            $table->enum('type', ['presentiel', 'visio', 'telephonique'])->default('presentiel');
            $table->enum('statut', ['planifie', 'realise', 'annule'])->default('planifie');
            $table->text('notes')->nullable();
            $table->integer('score')->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('entretiens');
    }
};