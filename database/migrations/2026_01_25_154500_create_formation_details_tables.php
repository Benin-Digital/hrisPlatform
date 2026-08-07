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
        Schema::create('sequence_formations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained('formations')->onDelete('cascade');
            $table->string('titre');
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });

        Schema::create('lecons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sequence_id')->constrained('sequence_formations')->onDelete('cascade');
            $table->string('titre');
            $table->text('contenu')->nullable();
            $table->string('video_url')->nullable();
            $table->integer('duree_minutes')->default(0);
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });

        Schema::create('inscription_formations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained('formations')->onDelete('cascade');
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->enum('statut', ['inscrit', 'en_cours', 'complete', 'abandonne'])->default('inscrit');
            $table->timestamp('termine_at')->nullable();
            $table->integer('progression_pourcentage')->default(0);
            $table->timestamps();

            $table->unique(['formation_id', 'utilisateur_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscription_formations');
        Schema::dropIfExists('lecons');
        Schema::dropIfExists('sequence_formations');
    }
};
