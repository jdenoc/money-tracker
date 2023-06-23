<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Reverse of 2015_09_23_014041_create_stamp_trigger.php
 */
return new class extends Migration {

    private const TRIGGER = 'trigger_entry_creation_timestamp';

    /**
     * Run the migrations.
     */
    public function up() {
        DB::unprepared("DROP TRIGGER IF EXISTS ".self::TRIGGER);
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        DB::unprepared(
            "CREATE TRIGGER ".self::TRIGGER."
            BEFORE INSERT ON entries
            FOR EACH ROW
            SET NEW.create_stamp = NOW()"
        );
    }

};
