<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttachmentColumnStamp extends Migration {

    private static $TABLE = 'attachments';
    private static $COLUMN_NEW = 'stamp';

    /**
     * Add column attachments.stamp
     *
     * @return void
     */
    public function up(){
        Schema::table(self::$TABLE, function (Blueprint $table) {
            $table->timestamp(self::$COLUMN_NEW)->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table(self::$TABLE, function (Blueprint $table) {
            $table->dropColumn(self::$COLUMN_NEW);
        });
    }

}