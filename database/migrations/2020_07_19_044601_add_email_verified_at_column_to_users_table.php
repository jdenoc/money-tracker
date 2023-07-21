<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailVerifiedAtColumnToUsersTable extends Migration {

    private static $TABLE = "users";
    private static $NEW_COLUMN = "email_verified_at";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->timestamp(self::$NEW_COLUMN)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->dropColumn(self::$NEW_COLUMN);
        });
    }

}
