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
        Schema::create('taches', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->foreignId('entite_id')->nullable()->constrained('entites')->onDelete('set null');
            $table->foreignId('createur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->foreignId('projet_id')->nullable(); // Si tu as un modèle Projet, sinon retire cette ligne
            $table->foreignId('assigne_a')->nullable()->constrained('utilisateurs')->onDelete('set null');
            $table->json('participants')->nullable(); // Array d'IDs
            $table->date('date_debut')->nullable();
            $table->date('date_echeance')->nullable();
            $table->date('date_fin_reelle')->nullable();
            $table->enum('priorite', ['basse', 'moyenne', 'haute'])->default('moyenne');
            $table->enum('statut', ['en_attente', 'en_cours', 'terminee', 'annulee'])->default('en_attente');
            $table->integer('progression_pourcentage')->default(0);
            $table->json('tags')->nullable(); // Array de strings
            $table->integer('estimation_heures')->nullable();
            $table->integer('temps_passe_minutes')->default(0);
            $table->json('fichiers_joints')->nullable(); // Array d'objets ou URLs
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taches');
    }
};