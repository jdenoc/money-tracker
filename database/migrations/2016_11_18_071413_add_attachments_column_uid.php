<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttachmentsColumnUid extends Migration {

    /**
     * Add column attachments.uid
     *
     * @return void
     */
    public function up(){
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('uid', 50)->after('attachment')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropColumn('uid');
        });
    }

}