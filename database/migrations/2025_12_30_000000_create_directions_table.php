<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('directions', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code_direction')->unique()->nullable();
            $table->foreignId('entite_id')->constrained('entites')->onDelete('cascade');
            $table->foreignId('directeur_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('direction_parent_id')->nullable()->constrained('directions')->onDelete('set null');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('directions');
    }
};