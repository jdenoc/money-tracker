<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccountsColumnCurrency extends Migration {

    private static $TABLE = "accounts";
    private static $NEW_COLUMN = "currency";

    /**
     * Add accounts.currency
     *
     * @return void
     */
    public function up() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->char(self::$NEW_COLUMN, 3)->after("total")->default("USD")
                ->comment("values conform to the ISO4217 standard");
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
