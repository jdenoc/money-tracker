<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEntriesColumnDisabledStamp extends Migration {

    private static $TABLE = 'entries';
    private static $COLUMN_NEW = 'disabled_stamp';

    /**
     * Add column entries.disabled_stamp
     * Assign a value to entries.disabled_stamp if entries.disabled == 1
     *
     * @return void
     */
    public function up(){
        Schema::table(self::$TABLE, function (Blueprint $table) {
            $table->timestamp(self::$COLUMN_NEW)->nullable();
        });
        DB::table(self::$TABLE)->where('disabled', 1)->update([self::$COLUMN_NEW=>DB::raw('modified_stamp')]);
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