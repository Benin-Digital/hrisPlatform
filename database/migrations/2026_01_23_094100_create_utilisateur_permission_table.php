<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Crée la table de liaison entre les utilisateurs et les permissions.
     */
    public function up(): void
    {
        Schema::create('utilisateur_permission', function (Blueprint $table) {
            $table->id(); // Clé primaire auto-incrémentée

            // Liaison vers la table 'users' (Laravel) – clé étrangère avec suppression en cascade
            $table->foreignId('utilisateur_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Liaison vers la table 'permissions' – clé étrangère avec suppression en cascade
            // Maintenant que la table 'permissions' existe (créée en premier), on peut la réactiver.
            $table->foreignId('permission_id')
                  ->constrained('permissions')
                  ->onDelete('cascade');

            // Date d'attribution de la permission (par défaut l'instant présent)
            $table->timestamp('assigne_le')->useCurrent();

            // Qui a attribué cette permission – clé étrangère vers 'users', peut être null
            $table->foreignId('assigne_par')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->timestamps(); // created_at et updated_at

            // Empêcher les doublons (un utilisateur ne peut avoir deux fois la même permission)
            $table->unique(['utilisateur_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     * Supprime la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateur_permission');
    }
};