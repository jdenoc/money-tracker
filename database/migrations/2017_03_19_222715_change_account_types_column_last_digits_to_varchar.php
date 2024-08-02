<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAccountTypesColumnLastDigitsToVarchar extends Migration {

    private static $COLUMN_NAME = 'last_digits';
    private static $TABLE_NAME = 'account_types';

    /**
     * Change the account_types.last_digits column from int to varchar(4)
     *
     * @return void
     */
    public function up() {
        Schema::table(self::$TABLE_NAME, function(Blueprint $table) {
            $table->string(self::$COLUMN_NAME, 4)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table(self::$TABLE_NAME, function(Blueprint $table) {
            $table->integer(self::$COLUMN_NAME)->nullable(false)->change();
        });
    }

}
