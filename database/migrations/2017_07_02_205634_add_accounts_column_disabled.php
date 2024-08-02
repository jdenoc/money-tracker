<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccountsColumnDisabled extends Migration {

    private static $TABLE = 'accounts';
    private static $NEW_COLUMN = 'disabled';

    /**
     * Add column accounts.disabled
     *
     * @return void
     */
    public function up() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->unsignedTinyInteger(self::$NEW_COLUMN)->after('institution_id')->default(0);
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
