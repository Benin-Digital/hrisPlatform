<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verifie_at')->nullable();
            $table->string('mot_de_passe');
            $table->string('nom');
            $table->string('prenom');
            $table->enum('civilite', ['M', 'Mme', 'Mlle'])->default('M');
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('photo_profil')->nullable();
            $table->string('telephone')->nullable();
            $table->string('telephone_urgence')->nullable();
            $table->text('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('code_postal')->nullable();
            $table->string('pays')->default('France');
            $table->foreignId('entite_id')->constrained('entites')->onDelete('cascade');
            $table->foreignId('direction_id')->nullable()->constrained('directions')->onDelete('set null');
            $table->string('poste')->nullable();
            $table->date('date_embauche')->nullable();
            $table->date('date_depart')->nullable();
            $table->enum('type_contrat', ['CDI', 'CDD', 'Stage', 'Alternance', 'Interim'])->default('CDI');
            $table->enum('statut', ['actif', 'inactif', 'suspendu', 'conges'])->default('actif');
            $table->enum('langue', ['fr', 'en'])->default('fr');
            $table->string('fuseau_horaire')->default('Europe/Paris');
            $table->enum('theme', ['clair', 'sombre', 'auto'])->default('clair');
            $table->boolean('notifications_email')->default(true);
            $table->boolean('notifications_push')->default(true);
            $table->boolean('deux_fa_active')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};