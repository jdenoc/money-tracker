<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    private const TABLE = 'institutions';
    private const COLUMN_TIMESTAMP_CREATE = 'create_stamp';
    private const COLUMN_TIMESTAMP_MODIFY = 'modified_stamp';

    /**
     * Run the migrations.
     */
    public function up() {
        // make sure create_stamp has a value
        DB::table(self::TABLE)
            ->whereNull(self::COLUMN_TIMESTAMP_CREATE)
            ->update([self::COLUMN_TIMESTAMP_CREATE => DB::raw(self::COLUMN_TIMESTAMP_MODIFY)]);

        // laravel currently can not handle table schema updates using an ORM approach
        $update_create_stamp_column_query = "ALTER TABLE %s CHANGE %s %s TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;";
        DB::statement(sprintf($update_create_stamp_column_query, self::TABLE, self::COLUMN_TIMESTAMP_CREATE, self::COLUMN_TIMESTAMP_CREATE));

        $update_modify_stamp_column_query = "ALTER TABLE %s CHANGE %s %s TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;";
        DB::statement(sprintf($update_modify_stamp_column_query, self::TABLE, self::COLUMN_TIMESTAMP_MODIFY, self::COLUMN_TIMESTAMP_MODIFY));
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        // laravel currently can not handle table schema updates using an ORM approach
        $update_create_stamp_column_query = "ALTER TABLE %s CHANGE %s %s TIMESTAMP NULL DEFAULT NULL;";
        DB::statement(sprintf($update_create_stamp_column_query, self::TABLE, self::COLUMN_TIMESTAMP_CREATE, self::COLUMN_TIMESTAMP_CREATE));

        $update_modify_stamp_column_query = "ALTER TABLE %s CHANGE %s %s TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;";
        DB::statement(sprintf($update_modify_stamp_column_query, self::TABLE, self::COLUMN_TIMESTAMP_MODIFY, self::COLUMN_TIMESTAMP_MODIFY));
    }

};
