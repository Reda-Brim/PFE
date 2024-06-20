<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mettre à jour les valeurs existantes pour qu'elles correspondent aux nouvelles valeurs ENUM
        DB::table('taches')->where('etat', 'en_cours')->update(['etat' => 'encours']);
        DB::table('taches')->where('etat', 'terminee')->update(['etat' => 'termine']);
        DB::table('taches')->where('etat', 'suspendue')->update(['etat' => 'todo']); // Si nécessaire, mettez à jour avec une valeur appropriée

        // Modifier la colonne `etat` pour utiliser le nouvel ENUM
        Schema::table('taches', function (Blueprint $table) {
            $table->enum('etat', ['todo', 'encours', 'toreview', 'termine'])->default('todo')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taches', function (Blueprint $table) {
            $table->enum('etat', ['en_cours', 'terminee', 'suspendue'])->default('en_cours')->change();
        });
    }
};
