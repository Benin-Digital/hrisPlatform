<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pointages', function (Blueprint $table) {
            $table->boolean('valide')->default(false);
            $table->foreignId('valide_par')->nullable()->constrained('utilisateurs')->nullOnDelete();
            $table->timestamp('valide_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pointages', function (Blueprint $table) {
            $table->dropForeign(['valide_par']);
            $table->dropColumn(['valide', 'valide_par', 'valide_at']);
        });
    }
};
