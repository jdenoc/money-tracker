<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccountsColumnInstitutionId extends Migration {

    private static $TABLE = 'accounts';
    private static $COLUMN_NEW = 'institution_id';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->unsignedInteger(self::$COLUMN_NEW)->after('account');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->dropColumn(self::$COLUMN_NEW);
        });
    }

}
