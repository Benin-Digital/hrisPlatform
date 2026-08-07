<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('nom_fichier');
            $table->string('nom_original');
            $table->string('extension');
            $table->string('mime_type');
            $table->bigInteger('taille_octets');
            $table->string('titre')->nullable();
            $table->text('description')->nullable();
            $table->text('mots_cles')->nullable();
            $table->string('auteur')->nullable();
            $table->string('langue')->default('fr');
            $table->foreignId('dossier_id')->constrained('dossiers_documents')->onDelete('cascade');
            $table->string('chemin_storage');
            $table->foreignId('proprietaire_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->foreignId('entite_id')->constrained('entites')->onDelete('cascade');
           $table->foreignId('direction_id')->nullable()->constrained('directions')->onDelete('set null');
            $table->integer('version_majeure')->default(1);
            $table->integer('version_mineure')->default(0);
            $table->integer('version_patch')->default(0);
            $table->boolean('permissions_heritees')->default(true);
            $table->integer('nombre_vues')->default(0);
            $table->integer('nombre_telechargements')->default(0);
            $table->timestamp('derniere_vue_at')->nullable();
            $table->boolean('est_verrouille')->default(false);
            $table->timestamp('date_expiration')->nullable();
            $table->boolean('est_archive')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};