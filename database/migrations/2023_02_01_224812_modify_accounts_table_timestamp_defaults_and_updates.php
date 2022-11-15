<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModifyAccountsTableTimestampDefaultsAndUpdates extends Migration {

    private static string $TABLE = 'accounts';
    private static string $COLUMN_TIMESTAMP_CREATE = 'create_stamp';
    private static string $COLUMN_TIMESTAMP_MODIFY = 'modified_stamp';

    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->timestamp(self::$COLUMN_TIMESTAMP_CREATE)
                ->nullable(false)
                ->default('CURRENT_TIMESTAMP')
                ->change();
        });

        // laravel currently can not handle table schema updates via existing methods, i.e.: ->useCurrent()->useCurrentOnUpdate()
        $update_modify_stamp_column_query = "ALTER TABLE %s CHANGE %s %s TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;";
        DB::statement(sprintf($update_modify_stamp_column_query, self::$TABLE, self::$COLUMN_TIMESTAMP_MODIFY, self::$COLUMN_TIMESTAMP_MODIFY));
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->timestamp(self::$COLUMN_TIMESTAMP_CREATE)->nullable()->default(null)->change();
            $table->timestamp(self::$COLUMN_TIMESTAMP_MODIFY)
                ->nullable(false)
                ->default('CURRENT_TIMESTAMP')
                ->change();
        });
    }

}
