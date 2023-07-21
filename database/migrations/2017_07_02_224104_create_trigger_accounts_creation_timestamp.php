<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTriggerAccountsCreationTimestamp extends Migration {

    private static $TRIGGER = 'trigger_accounts_creation_timestamp';

    /**
     * Create a MySQL trigger on the accounts table to add the current time to the accounts.create_stamp when inserting a new record
     *
     * @return void
     */
    public function up() {
        DB::unprepared(
            "CREATE TRIGGER ".self::$TRIGGER."
            BEFORE INSERT ON accounts
            FOR EACH ROW
            SET NEW.create_stamp = NOW()"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::unprepared("DROP TRIGGER IF EXISTS ".self::$TRIGGER);
    }

}
