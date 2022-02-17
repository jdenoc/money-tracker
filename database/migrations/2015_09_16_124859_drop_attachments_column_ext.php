<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropAttachmentsColumnExt extends Migration {

    private static $TABLE = 'attachments';
    private static $COLUMN = 'ext';

    /**
     * Drop column attachments.ext
     *
     * @return void
     */
    public function up(){
        Schema::table(self::$TABLE, function (Blueprint $table) {
            $table->dropColumn(self::$COLUMN);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table(self::$TABLE, function (Blueprint $table) {
            $table->string(self::$COLUMN, 10);
        });
    }

}