<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historique_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->enum('action', [
                'creation',
                'modification',
                'telechargement',
                'visualisation',
                'suppression',
                'restauration',
                'verrouillage',
                'deverrouillage',
                'partage'
            ])->notNull();
            $table->text('details')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_adresse')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historique_documents');
    }
};