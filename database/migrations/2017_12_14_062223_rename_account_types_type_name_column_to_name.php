<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameAccountTypesTypeNameColumnToName extends Migration {

    private static $TABLE_NAME = 'account_types';
    private static $COLUMN_NAME_ORIGINAL = 'type_name';
    private static $COLUMN_NAME_UPDATED = 'name';

    /**
     * Rename account_types.type_name to account_types.name
     *
     * @return void
     */
    public function up() {
        Schema::table(self::$TABLE_NAME, function(Blueprint $table) {
            $table->renameColumn(self::$COLUMN_NAME_ORIGINAL, self::$COLUMN_NAME_UPDATED);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table(self::$TABLE_NAME, function(Blueprint $table) {
            $table->renameColumn(self::$COLUMN_NAME_UPDATED, self::$COLUMN_NAME_ORIGINAL);
        });
    }

}
