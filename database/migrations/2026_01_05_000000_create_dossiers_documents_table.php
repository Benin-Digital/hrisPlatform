<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossiers_documents', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('nom');
            $table->foreignId('dossier_parent_id')->nullable()->constrained('dossiers_documents')->onDelete('cascade');
            $table->foreignId('entite_id')->constrained('entites')->onDelete('cascade');
            $table->foreignId('direction_id')->nullable()->constrained('directions')->onDelete('set null');
            $table->foreignId('creer_par')->constrained('utilisateurs')->onDelete('cascade');
            $table->enum('visibilite', ['public', 'entite', 'direction', 'prive', 'groupe'])->default('direction');
            $table->json('qui_peut_voir')->nullable();
            $table->json('qui_peut_ajouter')->nullable();
            $table->json('qui_peut_modifier')->nullable();
            $table->json('qui_peut_supprimer')->nullable();
            $table->bigInteger('quota_mo')->default(1024);
            $table->bigInteger('espace_utilise_mo')->default(0);
            $table->boolean('est_actif')->default(true);
            $table->boolean('est_archive')->default(false);
            $table->text('chemin_complet')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossiers_documents');
    }
};