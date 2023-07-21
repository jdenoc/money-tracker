<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateStampTrigger extends Migration {

    private static string $TRIGGER = 'trigger_entry_creation_timestamp';

    /**
     * Create a MySQL trigger to create a trigger on the entries table to add the current time to the entries.create_stamp when inserting a new record
     */
    public function up() {
        DB::unprepared(
            "CREATE TRIGGER ".self::$TRIGGER."
            BEFORE INSERT ON entries
            FOR EACH ROW
            SET NEW.create_stamp = NOW()"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        DB::unprepared("DROP TRIGGER IF EXISTS ".self::$TRIGGER);
    }

}
