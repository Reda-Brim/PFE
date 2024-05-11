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
        Schema::create('projets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sujet_id');
            $table->unsignedBigInteger('equipe_id');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->text('description')->nullable(); // Permettre la valeur NULL
            $table->enum('etat', ['en_cours', 'termine', 'suspendu'])->default('en_cours');
            $table->timestamps();

            $table->foreign('sujet_id')->references('id')->on('sujets')->onDelete('cascade');
            $table->foreign('equipe_id')->references('id')->on('equipes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projets');
    }
};
