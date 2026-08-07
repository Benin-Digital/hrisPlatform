<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partages_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->enum('partage_avec_type', ['utilisateur', 'groupe', 'direction', 'entite', 'role', 'extranet'])->notNull();
            $table->unsignedBigInteger('partage_avec_id');
            $table->foreignId('partage_par')->constrained('utilisateurs')->onDelete('cascade');
            $table->enum('permissions', ['lecture', 'telechargement', 'modification', 'gestion'])->default('lecture');
            $table->string('token_partage')->unique()->nullable();
            $table->timestamp('lien_public_expire_at')->nullable();
            $table->text('message')->nullable();
            $table->boolean('notifier')->default(true);
            $table->timestamp('notifie_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partages_documents');
    }
};