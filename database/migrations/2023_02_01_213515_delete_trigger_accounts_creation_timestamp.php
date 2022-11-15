<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Reverse of 2017_07_02_224104_create_trigger_accounts_creation_timestamp.php
 */
class DeleteTriggerAccountsCreationTimestamp extends Migration {

    private static string $TRIGGER = 'trigger_accounts_creation_timestamp';

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
            BEFORE INSERT ON accounts
            FOR EACH ROW
            SET NEW.create_stamp = NOW()"
        );
    }

}
