<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyEquipesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('equipes', function (Blueprint $table) {
            // Ajouter les colonnes seulement si elles n'existent pas
            if (!Schema::hasColumn('equipes', 'etudiant_1_codeApoge')) {
                $table->unsignedBigInteger('etudiant_1_codeApoge');
                $table->foreign('etudiant_1_codeApoge')->references('codeApoge')->on('etudiants')->onDelete('cascade');
            }
            if (!Schema::hasColumn('equipes', 'etudiant_2_codeApoge')) {
                $table->unsignedBigInteger('etudiant_2_codeApoge')->nullable();
                $table->foreign('etudiant_2_codeApoge')->references('codeApoge')->on('etudiants')->onDelete('cascade');
            }
            if (!Schema::hasColumn('equipes', 'etudiant_3_codeApoge')) {
                $table->unsignedBigInteger('etudiant_3_codeApoge')->nullable();
                $table->foreign('etudiant_3_codeApoge')->references('codeApoge')->on('etudiants')->onDelete('cascade');
            }
            if (!Schema::hasColumn('equipes', 'encadrant_code')) {
                $table->string('encadrant_code')->nullable();
                $table->foreign('encadrant_code')->references('encadrant_code')->on('encadrants')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipes', function (Blueprint $table) {
            if (Schema::hasColumn('equipes', 'etudiant_1_codeApoge')) {
                $table->dropForeign(['etudiant_1_codeApoge']);
                $table->dropColumn('etudiant_1_codeApoge');
            }
            if (Schema::hasColumn('equipes', 'etudiant_2_codeApoge')) {
                $table->dropForeign(['etudiant_2_codeApoge']);
                $table->dropColumn('etudiant_2_codeApoge');
            }
            if (Schema::hasColumn('equipes', 'etudiant_3_codeApoge')) {
                $table->dropForeign(['etudiant_3_codeApoge']);
                $table->dropColumn('etudiant_3_codeApoge');
            }
            if (Schema::hasColumn('equipes', 'encadrant_code')) {
                $table->dropForeign(['encadrant_code']);
                $table->dropColumn('encadrant_code');
            }
        });
    }
}
