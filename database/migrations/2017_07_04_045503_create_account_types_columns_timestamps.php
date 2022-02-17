<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountTypesColumnsTimestamps extends Migration {

    private static $TABLE = 'account_types';
    private static $COLUMN_CREATED = 'create_stamp';
    private static $COLUMN_MODIFIED = 'modified_stamp';
    private static $COLUMN_DISABLED = 'disabled_stamp';
    private static $COLUMN_LAST_UPDATE = 'last_updated';

    /**
     * Add column account_types.create_stamp
     * Renamed account_types.last_updated to account_types.modified_stamp
     * Add column account_types.disabled_stamp
     *
     * @return void
     */
    public function up(){
        Schema::table(self::$TABLE, function(Blueprint $table){
            $table->timestamp(self::$COLUMN_CREATED)->nullable()->after('disabled');
            $table->timestamp(self::$COLUMN_DISABLED)->nullable()->after(self::$COLUMN_LAST_UPDATE);
            $table->renameColumn(self::$COLUMN_LAST_UPDATE, self::$COLUMN_MODIFIED);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table(self::$TABLE, function(Blueprint $table){
            $table->dropColumn(self::$COLUMN_CREATED);
            $table->dropColumn(self::$COLUMN_DISABLED);
            $table->renameColumn(self::$COLUMN_MODIFIED, self::$COLUMN_LAST_UPDATE);
        });
    }

}