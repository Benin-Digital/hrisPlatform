<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('conges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->constrained('utilisateurs')->onDelete('cascade');
            $table->enum('type_conge', ['annuel', 'maladie', 'sans_solde', 'formation', 'autre'])->default('annuel');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('duree_ouvrable')->default(0)->comment('Nombre de jours ouvrés');
            $table->text('motif')->nullable();
            $table->enum('statut', ['en_attente', 'valide', 'rejete', 'annule'])->default('en_attente');
            $table->foreignId('valide_par')->nullable()->constrained('utilisateurs')->onDelete('set null');
            $table->timestamp('date_validation')->nullable();
            $table->text('commentaire_validation')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('conges');
    }
};