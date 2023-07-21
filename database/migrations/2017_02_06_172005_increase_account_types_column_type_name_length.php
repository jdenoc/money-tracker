<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IncreaseAccountTypesColumnTypeNameLength extends Migration {

    private static $TABLE_NAME = 'account_types';
    private static $COLUMN_NAME = 'type_name';

    /**
     * Increase account_types.type_name length to 100 characters
     *
     * @return void
     */
    public function up() {
        Schema::table(self::$TABLE_NAME, function(Blueprint $table) {
            $table->string(self::$COLUMN_NAME, 100)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        // Data won't be trimmed automatically, so we'll have to do it before shrinking type_name length
        DB::update("UPDATE ".self::$TABLE_NAME." SET ".self::$COLUMN_NAME."=SUBSTRING(".self::$COLUMN_NAME.", 0, 21)");
        Schema::table(self::$TABLE_NAME, function(Blueprint $table) {
            $table->string(self::$COLUMN_NAME, 21)->change();
        });
    }

}
