<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('candidatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offre_emploi_id')->constrained()->onDelete('cascade');
            $table->string('nom');
            $table->string('prenom');
            $table->string('email');
            $table->string('telephone')->nullable();
            $table->string('cv_path'); // Stockage du chemin du fichier
            $table->text('lettre_motivation')->nullable();
            $table->string('statut')->default('nouveau'); // nouveau, lu, entretien, rejete, embauche
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidatures');
    }
};
