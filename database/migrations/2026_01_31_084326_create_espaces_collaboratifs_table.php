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
        Schema::create('espaces_collaboratifs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('image_couverture')->nullable();
            $table->foreignId('createur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->foreignId('entite_id')->nullable()->constrained('entites')->onDelete('set null');
            $table->boolean('est_prive')->default(true);
            $table->enum('statut', ['actif', 'archive'])->default('actif');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('espaces_collaboratifs');
    }
};
