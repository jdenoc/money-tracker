<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropAttachmentsColumnExt extends Migration {

    /**
     * Drop column attachments.ext
     *
     * @return void
     */
    public function up(){
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropColumn('ext');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('ext', 10);
        });
    }

}