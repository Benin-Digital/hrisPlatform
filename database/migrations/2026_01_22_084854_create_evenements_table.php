<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evenements', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->dateTime('date_debut');
            $table->dateTime('date_fin');
            $table->string('fuseau_horaire')->default('Europe/Paris');
            $table->integer('duree_minutes')->nullable();
            $table->enum('type_evenement', ['reunion', 'formation', 'evenement_social', 'rendez_vous', 'autre'])->default('reunion');
            $table->string('categorie')->nullable();
            $table->string('couleur')->nullable();
            $table->string('lieu')->nullable();
            $table->string('lien_virtuel')->nullable();
            $table->enum('type_lieu', ['presentiel', 'virtuel', 'hybride'])->default('presentiel');
            $table->foreignId('organisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->foreignId('entite_id')->constrained('entites')->onDelete('cascade');
            $table->foreignId('direction_id')->nullable()->constrained('directions')->onDelete('set null');
            $table->foreignId('formation_id')->nullable()->constrained('formations')->onDelete('set null');
            $table->enum('visibilite', ['public', 'prive', 'groupe', 'direction'])->default('public');
            $table->json('groupes_acces')->nullable();
            $table->integer('capacite_max')->nullable();
            $table->boolean('inscription_requise')->default(false);
            $table->boolean('est_recurrent')->default(false);
            $table->json('recurrence_pattern')->nullable();
            $table->date('date_fin_recurrence')->nullable();
            $table->enum('statut', ['planifie', 'confirme', 'annule', 'termine'])->default('planifie');
            $table->integer('nombre_participants')->default(0);
            $table->integer('nombre_inscrits')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evenements');
    }
};