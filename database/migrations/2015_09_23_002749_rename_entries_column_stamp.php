<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameEntriesColumnStamp extends Migration {

    private static $TABLE = 'entries';
    private static $COLUMN_CREATE = 'create_stamp';
    private static $COLUMN_MODIFIED = 'modified_stamp';
    private static $COLUMN_STAMP = 'stamp';

    /**
     * Add column entries.create_stamp
     * Move values from entries.stamp to entries.create_stamp
     * Delete column entries.stamp
     *
     * @return void
     */
    public function up() {
        // add a new column
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->timestamp(self::$COLUMN_CREATE)->nullable();
            $table->timestamp(self::$COLUMN_MODIFIED)->useCurrent();
        });

        // move data from old column to new column
        DB::table(self::$TABLE)->update([self::$COLUMN_CREATE => DB::raw(self::$COLUMN_STAMP)]);

        // delete old column
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->dropColumn(self::$COLUMN_STAMP);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        // add a new column
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->timestamp(self::$COLUMN_STAMP)->useCurrent();
        });

        // move data from old column to new column
        DB::table(self::$TABLE)->update([self::$COLUMN_STAMP => DB::raw(self::$COLUMN_CREATE)]);

        // delete old column
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->dropColumn(self::$COLUMN_CREATE);
            $table->dropColumn(self::$COLUMN_MODIFIED);
        });
    }

}
