<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameEntriesColumnDate extends Migration {

    private static $TABLE = 'entries';
    private static $COLUMN_OLD = 'date';
    private static $COLUMN_NEW = 'entry_date';
    private static $INDEX_OLD = 'date';
    private static $INDEX_NEW = 'entry_date';

    /**
     * Rename column entries.date to entries.entry_date
     *
     * @return void
     */
    public function up() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->dropIndex([self::$INDEX_OLD]);
            $table->renameColumn(self::$COLUMN_OLD, self::$COLUMN_NEW);
            $table->index(self::$INDEX_NEW);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->dropIndex([self::$INDEX_NEW]);
            $table->renameColumn(self::$COLUMN_NEW, self::$COLUMN_OLD);
            $table->index(self::$INDEX_OLD);
        });
    }

}
