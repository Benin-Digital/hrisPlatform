<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pointages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->date('date');
            $table->time('heure_entree')->nullable();
            $table->time('heure_sortie')->nullable();
            $table->enum('statut', ['present', 'absent', 'retard', 'conges', 'ferie'])->default('present');
            $table->text('commentaire')->nullable();
            $table->timestamps();

            $table->unique(['utilisateur_id', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pointages');
    }
};