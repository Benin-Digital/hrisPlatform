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
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('espace_id')->nullable()->after('direction_id')->constrained('espaces_collaboratifs')->onDelete('cascade');
        });

        Schema::table('taches', function (Blueprint $table) {
            $table->foreignId('espace_id')->nullable()->after('projet_id')->constrained('espaces_collaboratifs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['espace_id']);
            $table->dropColumn('espace_id');
        });

        Schema::table('taches', function (Blueprint $table) {
            $table->dropForeign(['espace_id']);
            $table->dropColumn('espace_id');
        });
    }
};
