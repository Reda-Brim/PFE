<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyLienColumnInDocumentsTable extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->text('lien')->change(); // Change to text
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('lien', 255)->change(); // Revert back to the original length if necessary
        });
    }
}
