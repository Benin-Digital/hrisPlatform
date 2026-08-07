<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->foreignId('offre_emploi_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('candidatures', function (Blueprint $table) {
            $table->foreignId('offre_emploi_id')->nullable(false)->change();
        });
    }
};