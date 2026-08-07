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
        Schema::create('espace_membres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('espace_id')->constrained('espaces_collaboratifs')->onDelete('cascade');
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->enum('role', ['admin', 'membre'])->default('membre');
            $table->timestamp('date_rejoint')->useCurrent();
            $table->timestamps();

            $table->unique(['espace_id', 'utilisateur_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('espace_membres');
    }
};
