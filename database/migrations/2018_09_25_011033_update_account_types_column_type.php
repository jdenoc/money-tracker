<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateAccountTypesColumnType extends Migration {

    private static $TABLE = 'account_types';
    private static $COLUMN = 'type';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        // ORM does not currently allow modification of ENUM type columns
        DB::statement("ALTER TABLE ".self::$TABLE." CHANGE ".self::$COLUMN." ".self::$COLUMN." ENUM('checking','savings','credit card','debit card','loan')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        // ORM does not currently allow modification of ENUM type columns
        DB::statement("ALTER TABLE ".self::$TABLE." CHANGE ".self::$COLUMN." ".self::$COLUMN." ENUM('checking','savings','credit card','debit card')");
    }

}
