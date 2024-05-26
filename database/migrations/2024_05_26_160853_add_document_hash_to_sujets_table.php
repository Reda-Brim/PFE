<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocumentHashToSujetsTable extends Migration
{
    public function up()
    {
        Schema::table('sujets', function (Blueprint $table) {
            if (!Schema::hasColumn('sujets', 'document_hash')) {
                $table->string('document_hash')->unique()->after('document');
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
