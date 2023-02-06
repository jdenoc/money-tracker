<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateAccountTypesTimestampDefaultsAndUpdates extends Migration {

    private const TABLE = 'account_types';
    private const COLUMN_TIMESTAMP_CREATE = 'create_stamp';
    private const COLUMN_TIMESTAMP_MODIFY = 'modified_stamp';

    /**
     * Run the migrations.
     */
    public function up() {
        // make sure create_stamp has a value
        DB::table(self::TABLE)
            ->whereNull(self::COLUMN_TIMESTAMP_CREATE)
            ->update([self::COLUMN_TIMESTAMP_CREATE=>DB::raw(self::COLUMN_TIMESTAMP_MODIFY)]);

        // alter the create_stamp column default
        Schema::table(self::TABLE, function(Blueprint $table) {
            $table->timestamp(self::COLUMN_TIMESTAMP_CREATE)
                ->nullable(false)
                ->default("CURRENT_TIMESTAMP")
                ->change();
        });

        // laravel currently can not handle table schema updates via existing methods, i.e.: ->useCurrent()->useCurrentOnUpdate()
        $update_modify_stamp_column_query = "ALTER TABLE %s CHANGE %s %s TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;";
        DB::statement(sprintf($update_modify_stamp_column_query, self::TABLE, self::COLUMN_TIMESTAMP_MODIFY, self::COLUMN_TIMESTAMP_MODIFY));
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table(self::TABLE, function(Blueprint $table) {
            $table->timestamp(self::COLUMN_TIMESTAMP_CREATE)->nullable()->default(null)->change();
            $table->timestamp(self::COLUMN_TIMESTAMP_MODIFY)
                ->nullable(false)
                ->default('CURRENT_TIMESTAMP')
                ->change();
        });
    }

}
