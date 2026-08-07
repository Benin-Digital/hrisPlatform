<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('soldes_conges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->enum('type_conge', ['annuel', 'maladie', 'sans_solde', 'formation', 'autre'])->default('annuel');
            $table->year('annee');
            $table->integer('solde_initial')->default(0);
            $table->integer('solde_pris')->default(0);
            $table->integer('solde_restant')->default(0);
            $table->timestamps();

            $table->unique(['utilisateur_id', 'type_conge', 'annee']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('soldes_conges');
    }
};