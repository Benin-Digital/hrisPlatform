<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partages_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('partage_avec_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('partages_documents', function (Blueprint $table) {
            // Reviens à NOT NULL (attention : rollback peut échouer si des NULL existent déjà)
            $table->unsignedBigInteger('partage_avec_id')->nullable(false)->change();
        });
    }
};