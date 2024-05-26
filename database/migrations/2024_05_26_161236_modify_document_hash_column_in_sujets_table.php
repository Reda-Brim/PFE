<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyDocumentHashColumnInSujetsTable extends Migration
{
    public function up()
    {
        Schema::table('sujets', function (Blueprint $table) {
            // Assurez-vous que la colonne existe et qu'elle est unique
            if (!Schema::hasColumn('sujets', 'document_hash')) {
                $table->string('document_hash')->unique()->after('document');
            } else {
                $table->string('document_hash')->unique()->change();
            }
        });
    }

    public function down()
    {
        Schema::table('sujets', function (Blueprint $table) {
            if (Schema::hasColumn('sujets', 'document_hash')) {
                $table->dropColumn('document_hash');
            }
        });
    }
}
