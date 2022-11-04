<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUsersIdColumnToBigInteger extends Migration {

    private static $TABLE = 'users';
    private static $COLUMN = 'id';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->bigIncrements(self::$COLUMN)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->increments(self::$COLUMN)->change();
        });
    }

}
