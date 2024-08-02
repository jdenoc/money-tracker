<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameAccountTypesColumnAccountGroup extends Migration {

    private static $TABLE = 'account_types';
    private static $COLUMN_OLD_NAME = 'account_group';
    private static $COLUMN_NEW_NAME = 'account_id';

    /**
     * Renamed account_types.account_group to account_types.account_id
     *
     * @return void
     */
    public function up() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->renameColumn(self::$COLUMN_OLD_NAME, self::$COLUMN_NEW_NAME);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->renameColumn(self::$COLUMN_NEW_NAME, self::$COLUMN_OLD_NAME);
        });
    }

}
