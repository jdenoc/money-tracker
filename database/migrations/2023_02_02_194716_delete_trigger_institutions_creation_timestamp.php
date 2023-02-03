<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Reverse of 2017_06_26_174109_create_trigger_institute_creation_timestamp.php
 */
class DeleteTriggerInstitutionsCreationTimestamp extends Migration {

    private static string $TRIGGER = 'trigger_institute_creation_timestamp';

    /**
     * Run the migrations.
     */
    public function up() {
        DB::unprepared("DROP TRIGGER IF EXISTS ".self::$TRIGGER);
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        DB::unprepared(
            "CREATE TRIGGER ".self::$TRIGGER."
            BEFORE INSERT ON institutions
            FOR EACH ROW
            SET NEW.create_stamp = NOW()"
        );
    }

}
