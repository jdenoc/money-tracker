<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameEntriesColumnValue extends Migration {

    private static $TABLE = 'entries';
    private static $COLUMN_NEW = 'entry_value';
    private static $COLUMN_OLD = 'value';

    /**
     * Rename column entries.value to entries.entry_value
     *
     * @return void
     */
    public function up() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->renameColumn(self::$COLUMN_OLD, self::$COLUMN_NEW);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->renameColumn(self::$COLUMN_NEW, self::$COLUMN_OLD);
        });
    }

}
