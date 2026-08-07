<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            if (!Schema::hasColumn('annonces', 'type_annonce')) {
                $table->enum('type_annonce', ['information', 'urgent', 'evenement', 'rh', 'autre'])
                      ->default('information');
            }

            if (!Schema::hasColumn('annonces', 'cible_type')) {
                $table->enum('cible_type', ['tous', 'direction', 'groupes', 'utilisateurs'])
                      ->default('tous');
            }

            if (!Schema::hasColumn('annonces', 'direction_id')) {
                $table->foreignId('direction_id')
                      ->nullable()
                      ->constrained('directions')
                      ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            $table->dropForeign(['direction_id']);
            $table->dropColumn(['type_annonce', 'cible_type', 'direction_id']);
        });
    }
};
