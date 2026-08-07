<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annonces', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->longText('contenu');
            $table->foreignId('entite_id')->constrained('entites')->onDelete('cascade');
            $table->foreignId('auteur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->boolean('est_epingle')->default(false);
            $table->timestamp('date_publication')->default(now());  // ← AJOUTE CETTE LIGNE ICI
            $table->json('utilisateurs_cibles')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annonces');
    }
};