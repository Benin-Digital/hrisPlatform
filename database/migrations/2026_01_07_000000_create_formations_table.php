<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formations', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('titre');
            $table->string('slug')->unique();
            $table->text('sous_titre')->nullable();
            $table->longText('description')->nullable();
            $table->text('objectifs')->nullable();
            $table->text('prerequis')->nullable();
            $table->foreignId('categorie_id')->nullable()->constrained('categories_formations')->onDelete('set null');
            $table->foreignId('formateur_principal_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->foreignId('entite_id')->constrained('entites')->onDelete('cascade');
            $table->enum('niveau', ['debutant', 'intermediaire', 'avance', 'expert'])->default('debutant');
            $table->integer('duree_minutes');
            $table->integer('points_competences')->default(0);
            $table->decimal('cout', 10, 2)->nullable();
            $table->string('devise')->default('EUR');
            $table->string('image_couverture')->nullable();
            $table->string('video_presentation')->nullable();
            $table->string('lien_session')->nullable();
            $table->json('fichiers_joints')->nullable();
            $table->boolean('est_public')->default(false);
            $table->enum('mode_acces', ['interne', 'externe', 'mixte', 'libre', 'inscription', 'approbation', 'payant'])->default('interne');
            $table->integer('capacite_max')->nullable();
            $table->boolean('certificat_disponible')->default(false);
            $table->boolean('evaluation_obligatoire')->default(false);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->date('date_limite_inscription')->nullable();
            $table->integer('nombre_vues')->default(0);
            $table->integer('nombre_inscrits')->default(0);
            $table->decimal('note_moyenne', 3, 2)->default(0);
            $table->integer('nombre_evaluations')->default(0);
            $table->enum('statut', ['brouillon', 'publie', 'archive', 'planifie', 'en_cours', 'termine'])->default('brouillon');
            $table->timestamp('publie_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};