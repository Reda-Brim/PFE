<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyDocumentColumnInSujetsTable extends Migration
{
    public function up()
    {
        Schema::table('sujets', function (Blueprint $table) {
            $table->text('document')->change();
        });
    }

    public function down()
    {
        Schema::table('sujets', function (Blueprint $table) {
            $table->string('document', 255)->change();
        });
    }
}
