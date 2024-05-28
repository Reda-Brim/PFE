<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveOldColumnsFromEquipesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('equipes', function (Blueprint $table) {
            // Vérifiez si les colonnes existent avant de les supprimer
            if (Schema::hasColumn('equipes', 'etudiant_1_id')) {
                $table->dropForeign(['etudiant_1_id']);
                $table->dropColumn('etudiant_1_id');
            }
            if (Schema::hasColumn('equipes', 'etudiant_2_id')) {
                $table->dropForeign(['etudiant_2_id']);
                $table->dropColumn('etudiant_2_id');
            }
            if (Schema::hasColumn('equipes', 'etudiant_3_id')) {
                $table->dropForeign(['etudiant_3_id']);
                $table->dropColumn('etudiant_3_id');
            }
            if (Schema::hasColumn('equipes', 'encadrant_id')) {
                $table->dropForeign(['encadrant_id']);
                $table->dropColumn('encadrant_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipes', function (Blueprint $table) {
            // Recréez les colonnes si vous annulez la migration
            if (!Schema::hasColumn('equipes', 'etudiant_1_id')) {
                $table->unsignedBigInteger('etudiant_1_id');
                $table->foreign('etudiant_1_id')->references('id')->on('etudiants')->onDelete('cascade');
            }
            if (!Schema::hasColumn('equipes', 'etudiant_2_id')) {
                $table->unsignedBigInteger('etudiant_2_id')->nullable();
                $table->foreign('etudiant_2_id')->references('id')->on('etudiants')->onDelete('cascade');
            }
            if (!Schema::hasColumn('equipes', 'etudiant_3_id')) {
                $table->unsignedBigInteger('etudiant_3_id')->nullable();
                $table->foreign('etudiant_3_id')->references('id')->on('etudiants')->onDelete('cascade');
            }
            if (!Schema::hasColumn('equipes', 'encadrant_id')) {
                $table->unsignedBigInteger('encadrant_id')->nullable();
                $table->foreign('encadrant_id')->references('id')->on('encadrants')->onDelete('cascade');
            }
        });
    }
    
}
