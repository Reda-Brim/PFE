<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEncadrantCodeToEncadrantsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('encadrants', function (Blueprint $table) {
            if (!Schema::hasColumn('encadrants', 'encadrant_code')) {
                $table->string('encadrant_code')->unique()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('encadrants', function (Blueprint $table) {
            if (Schema::hasColumn('encadrants', 'encadrant_code')) {
                $table->dropColumn('encadrant_code');
            }
        });
    }
}
