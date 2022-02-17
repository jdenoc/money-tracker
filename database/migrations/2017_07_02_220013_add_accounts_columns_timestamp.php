<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccountsColumnsTimestamp extends Migration {

    private static $TABLE = 'accounts';
    private static $COLUMN_TIMESTAMP_CREATE = 'create_stamp';
    private static $COLUMN_TIMESTAMP_MODIFY = 'modified_stamp';
    private static $COLUMN_TIMESTAMP_DISABLE = 'disabled_stamp';

    /**
     * Add column accounts.create_stamp
     * Add column accounts.modified_stamp
     * Add column accounts.disabled_stamp
     *
     * @return void
     */
    public function up(){
        Schema::table(self::$TABLE, function (Blueprint $table) {
            $table->timestamp(self::$COLUMN_TIMESTAMP_CREATE)->nullable();
            $table->timestamp(self::$COLUMN_TIMESTAMP_MODIFY)->useCurrent();
            $table->timestamp(self::$COLUMN_TIMESTAMP_DISABLE)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table(self::$TABLE, function (Blueprint $table) {
            $table->dropColumn(self::$COLUMN_TIMESTAMP_CREATE);
            $table->dropColumn(self::$COLUMN_TIMESTAMP_MODIFY);
            $table->dropColumn(self::$COLUMN_TIMESTAMP_DISABLE);
        });
    }

}