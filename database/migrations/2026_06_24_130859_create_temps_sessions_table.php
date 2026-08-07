<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('temps_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tache_id')->constrained('taches')->onDelete('cascade');
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->timestamp('debut')->nullable();
            $table->timestamp('fin')->nullable();
            $table->integer('duree_secondes')->default(0);
            $table->boolean('est_en_cours')->default(false);
            $table->timestamps();

            $table->index(['tache_id', 'est_en_cours']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('temps_sessions');
    }
};