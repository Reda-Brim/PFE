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
        Schema::create('equipes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('etudiant_1_id');
            $table->unsignedBigInteger('etudiant_2_id')->nullable();
            $table->unsignedBigInteger('etudiant_3_id')->nullable();
            $table->unsignedBigInteger('encadrant_id');
            $table->timestamps();

            $table->foreign('etudiant_1_id')->references('id')->on('etudiants');
            $table->foreign('etudiant_2_id')->references('id')->on('etudiants');
            $table->foreign('etudiant_3_id')->references('id')->on('etudiants');
            $table->foreign('encadrant_id')->references('id')->on('encadrants');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipes');
    }
};
